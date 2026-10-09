<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PriceReductionRequest;
use App\Contracts\Repositories\PriceReductionRequestRepositoryInterface;
use App\Contracts\Repositories\ReductionTierRepositoryInterface;
use App\Events\ReductionRequestedEvent;
use App\Events\ReductionRespondedEvent;

class PriceReductionService
{
    protected $requestRepo;
    protected $tierRepo;

    public function __construct(PriceReductionRequestRepositoryInterface $requestRepo, ReductionTierRepositoryInterface $tierRepo)
    {
        $this->requestRepo = $requestRepo;
        $this->tierRepo = $tierRepo;
    }

    public function getAvailableTiers()
    {
        return $this->tierRepo->getAllActive();
    }

    public function createRequest(Order $order, $customer, $requestedReduction)
    {
        $vendorId = $order->seller_is == 'admin' ? 1 : $order->seller_id;
        $data = [
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'vendor_id' => $vendorId,
            'original_price' => $order->order_amount,
            'requested_reduction' => $requestedReduction,
            'status' => PriceReductionRequest::STATUS_PENDING,
            'round_number' => 1
        ];
        $request = $this->requestRepo->create($data);
        event(new ReductionRequestedEvent($request, $order, $customer));
        return $request;
    }

    public function createCartRequest(\App\Models\Cart $cart, $customer, $requestedReduction)
    {
        $vendorId = ($cart->seller_is == 'admin') ? 1 : $cart->seller_id;

        $existing = PriceReductionRequest::where('cart_id', $cart->id)
            ->whereIn('status', [PriceReductionRequest::STATUS_PENDING, PriceReductionRequest::STATUS_COUNTER_OFFER])
            ->first();

        if ($existing) {
            throw new \Exception("Une demande de réduction est déjà en cours pour ce produit.");
        }

        $data = [
            'cart_id' => $cart->id,
            'product_id' => $cart->product_id,
            'order_id' => null,
            'customer_id' => $customer->id,
            'vendor_id' => $vendorId,
            'original_price' => $cart->price,
            'requested_reduction' => $requestedReduction,
            'proposed_price' => max(0, $cart->price - $requestedReduction),
            'status' => PriceReductionRequest::STATUS_PENDING,
            'round_number' => 1
        ];
        $request = $this->requestRepo->create($data);
        event(new ReductionRequestedEvent($request, null, $customer));
        return $request;
    }

    public function acceptRequest(PriceReductionRequest $request)
    {
        $this->requestRepo->update($request->id, ['status' => PriceReductionRequest::STATUS_ACCEPTED]);
        if ($request->cart_id) {
            $cart = \App\Models\Cart::find($request->cart_id);
            if ($cart) {
                $cart->discount = floatval($request->requested_reduction);
                $cart->save();
            }
        } elseif ($request->order_id) {
            $order = Order::find($request->order_id);
            if ($order) {
                $reduction = floatval($request->requested_reduction);
                $order->order_amount = max(0, $order->order_amount - $reduction);
                $order->discount_amount = ($order->discount_amount ?? 0) + $reduction;
                $order->save();
            }
        }
        event(new ReductionRespondedEvent($request, PriceReductionRequest::STATUS_ACCEPTED));
        return $request;
    }

    public function refuseRequest(PriceReductionRequest $request)
    {
        $this->requestRepo->update($request->id, ['status' => PriceReductionRequest::STATUS_REFUSED]);
        event(new ReductionRespondedEvent($request, PriceReductionRequest::STATUS_REFUSED));
        return $request;
    }

    public function counterOffer(PriceReductionRequest $request, $counterAmount)
    {
        if ($request->round_number >= 2) {
            throw new \Exception("Maximum negotiation rounds reached.");
        }
        
        $this->requestRepo->update($request->id, [
            'status' => PriceReductionRequest::STATUS_COUNTER_OFFER,
            'counter_offer_amount' => $counterAmount,
            'round_number' => $request->round_number + 1
        ]);
        
        event(new ReductionRespondedEvent($request, PriceReductionRequest::STATUS_COUNTER_OFFER));
        return $request;
    }

    public function respondToCounterOffer(PriceReductionRequest $request, bool $accept)
    {
        $status = $accept ? PriceReductionRequest::STATUS_ACCEPTED : PriceReductionRequest::STATUS_REFUSED;
        $this->requestRepo->update($request->id, ['status' => $status]);
        if ($accept && $request->cart_id && $request->counter_offer_amount) {
            $cart = \App\Models\Cart::find($request->cart_id);
            if ($cart) {
                $cart->discount = floatval($request->counter_offer_amount);
                $cart->save();
            }
        } elseif ($accept && $request->order_id && $request->counter_offer_amount) {
            $order = Order::find($request->order_id);
            if ($order) {
                $reduction = floatval($request->counter_offer_amount);
                $order->order_amount = max(0, $order->order_amount - $reduction);
                $order->discount_amount = ($order->discount_amount ?? 0) + $reduction;
                $order->save();
            }
        }
        event(new ReductionRespondedEvent($request, $status));
        return $request;
    }
}
