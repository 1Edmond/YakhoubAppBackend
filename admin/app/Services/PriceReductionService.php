<?php

namespace App\Services;

use App\Events\ReductionRequestedEvent;
use App\Events\ReductionRespondedEvent;
use App\Exceptions\ReductionException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PriceReductionRequest as Req;
use App\Models\ReductionTier;
use Illuminate\Support\Facades\DB;

class PriceReductionService
{
    public function getAvailableTiers()
    {
        return ReductionTier::where('is_active', true)->orderBy('tier_number')->get();
    }

    private function assertValidTier(float $amount): void
    {
        $tiers = ReductionTier::where('is_active', true)->pluck('amount')
            ->map(fn ($a) => round((float) $a, 2))->all();

        if (!in_array(round($amount, 2), $tiers, true)) {
            throw new ReductionException(translate('reduction_amount_not_allowed'), 422);
        }
    }

    private function lockFresh(Req $request): Req
    {
        return Req::whereKey($request->id)->lockForUpdate()->firstOrFail();
    }

    /** Réduction sur le total d'une commande (CDC 3.4.7 règle 1). */
    public function createRequest(Order $order, $customer, float $requestedReduction): Req
    {
        $request = DB::transaction(function () use ($order, $customer, $requestedReduction) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ((int) $order->customer_id !== (int) $customer->id) {
                throw new ReductionException(translate('order_not_found'), 404);
            }
            if ($order->order_status !== 'pending') {
                throw new ReductionException(translate('status_not_changeable_now'), 422);
            }
            $this->assertValidTier($requestedReduction);
            if ($requestedReduction >= (float) $order->order_amount) {
                throw new ReductionException(translate('reduction_exceeds_price'), 422);
            }
            if (Req::where('order_id', $order->id)->active()->exists()) {
                throw new ReductionException(translate('reduction_already_in_progress'), 422);
            }

            return Req::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'vendor_id' => $order->seller_is == 'admin' ? 1 : $order->seller_id,
                'original_price' => $order->order_amount,
                'requested_reduction' => $requestedReduction,
                'proposed_price' => $order->order_amount - $requestedReduction,
                'status' => Req::STATUS_PENDING,
                'round_number' => 1,
            ]);
        });

        event(new ReductionRequestedEvent($request, $request->order, $customer));
        return $request;
    }

    /** Réduction sur une ligne de panier (variante par produit implémentée par l'app). */
    public function createCartRequest(Cart $cart, $customer, float $requestedReduction): Req
    {
        $request = DB::transaction(function () use ($cart, $customer, $requestedReduction) {
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            if ((string) $cart->customer_id !== (string) $customer->id) {
                throw new ReductionException(translate('not_found'), 404);
            }
            $this->assertValidTier($requestedReduction);

            $unitPrice = (float) $cart->price;
            $baseDiscount = (float) $cart->discount;
            if ($requestedReduction >= ($unitPrice - $baseDiscount)) {
                throw new ReductionException(translate('reduction_exceeds_price'), 422);
            }
            if (Req::where('cart_id', $cart->id)->whereIn('status', [Req::STATUS_PENDING, Req::STATUS_COUNTER_OFFER, Req::STATUS_ACCEPTED])->exists()) {
                throw new ReductionException(translate('reduction_already_in_progress'), 422);
            }

            return Req::create([
                'cart_id' => $cart->id,
                'product_id' => $cart->product_id,
                'cart_base_discount' => $baseDiscount,
                'order_id' => null,
                'customer_id' => $customer->id,
                'vendor_id' => $cart->seller_is == 'admin' ? 1 : $cart->seller_id,
                'original_price' => $unitPrice,
                'requested_reduction' => $requestedReduction,
                'proposed_price' => $unitPrice - $baseDiscount - $requestedReduction,
                'status' => Req::STATUS_PENDING,
                'round_number' => 1,
            ]);
        });

        event(new ReductionRequestedEvent($request, null, $customer));
        return $request;
    }

    public function acceptRequest(Req $request): Req
    {
        $request = DB::transaction(function () use ($request) {
            $fresh = $this->lockFresh($request);
            $this->assertStatus($fresh, [Req::STATUS_PENDING]);
            $fresh->update(['status' => Req::STATUS_ACCEPTED]);
            $this->applyReduction($fresh, (float) $fresh->requested_reduction);
            return $fresh;
        });

        event(new ReductionRespondedEvent($request, Req::STATUS_ACCEPTED));
        return $request;
    }

    public function refuseRequest(Req $request): Req
    {
        $request = DB::transaction(function () use ($request) {
            $fresh = $this->lockFresh($request);
            $this->assertStatus($fresh, [Req::STATUS_PENDING]);
            $fresh->update(['status' => Req::STATUS_REFUSED]);
            return $fresh;
        });

        event(new ReductionRespondedEvent($request, Req::STATUS_REFUSED));
        return $request;
    }

    /** Le vendeur propose un autre palier, strictement inférieur à la demande (CDC 3.4.7 règle 4). */
    public function counterOffer(Req $request, float $counterAmount): Req
    {
        $request = DB::transaction(function () use ($request, $counterAmount) {
            $fresh = $this->lockFresh($request);
            $this->assertStatus($fresh, [Req::STATUS_PENDING]);

            if ($fresh->round_number >= config('multishop.max_negotiation_rounds', 2)) {
                throw new ReductionException(translate('max_negotiation_rounds_reached'), 422);
            }
            $this->assertValidTier($counterAmount);
            if ($counterAmount >= (float) $fresh->requested_reduction) {
                throw new ReductionException(translate('counter_offer_must_be_lower'), 422);
            }

            $fresh->update([
                'status' => Req::STATUS_COUNTER_OFFER,
                'counter_offer_amount' => $counterAmount,
                'proposed_price' => (float) $fresh->original_price - (float) $fresh->cart_base_discount - $counterAmount,
                'round_number' => $fresh->round_number + 1,
            ]);
            return $fresh;
        });

        event(new ReductionRespondedEvent($request, Req::STATUS_COUNTER_OFFER));
        return $request;
    }

    /** Le client accepte ou refuse la contre-proposition. Appliquée UNE seule fois. */
    public function respondToCounterOffer(Req $request, bool $accept): Req
    {
        $request = DB::transaction(function () use ($request, $accept) {
            $fresh = $this->lockFresh($request);
            $this->assertStatus($fresh, [Req::STATUS_COUNTER_OFFER]);

            if (!$accept) {
                $fresh->update(['status' => Req::STATUS_REFUSED]);
                return $fresh;
            }
            $fresh->update(['status' => Req::STATUS_ACCEPTED]);
            $this->applyReduction($fresh, (float) $fresh->counter_offer_amount);
            return $fresh;
        });

        event(new ReductionRespondedEvent($request, $request->status));
        return $request;
    }

    private function assertStatus(Req $request, array $allowed): void
    {
        if (!in_array($request->status, $allowed, true)) {
            throw new ReductionException(translate('negotiation_already_processed'), 422);
        }
    }

    private function applyReduction(Req $request, float $amount): void
    {
        if ($request->applied_at !== null) {
            throw new ReductionException(translate('negotiation_already_processed'), 422);
        }

        if ($request->cart_id) {
            $cart = Cart::whereKey($request->cart_id)->lockForUpdate()->first();
            if ($cart) {
                // on AJOUTE à la remise native du produit (l'ancienne version l'écrasait)
                $cart->discount = (float) $request->cart_base_discount + $amount;
                $cart->save();
            }
        } elseif ($request->order_id) {
            $order = Order::whereKey($request->order_id)->lockForUpdate()->first();
            if ($order) {
                $order->order_amount = max(0, (float) $order->order_amount - $amount);
                $order->discount_amount = (float) ($order->discount_amount ?? 0) + $amount;
                $order->has_reduction = true;
                $order->reduction_amount = $amount;
                $order->final_price = $order->order_amount;
                $order->save();
            }
        }

        $request->forceFill(['applied_at' => now(), 'applied_reduction' => $amount])->save();
    }
}
