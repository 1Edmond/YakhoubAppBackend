<?php

namespace App\Services;

use App\Exceptions\ManualTransferException;
use App\Models\Order;
use App\Models\OrderTransferProof;
use App\Models\TransferAccount;
use App\Models\TransferOperator;
use App\Utils\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ManualTransferService
{
    /** Frais de l'opérateur : TOUJOURS calculés côté serveur. */
    public function computeFee(?TransferOperator $operator, float $amount): float
    {
        if (!$operator) {
            return 0.0;
        }

        $tier = $operator->fees->first(function ($fee) use ($amount) {
            if ($amount < (float) $fee->min_amount) {
                return false;
            }
            return !($fee->max_amount !== null && $amount > (float) $fee->max_amount);
        });

        if (!$tier) {
            return 0.0;
        }

        $fee = $tier->fee_type === 'percent'
            ? $amount * (float) $tier->fee_amount / 100
            : (float) $tier->fee_amount;

        return round($fee, 2);
    }

    /** Comptes actifs ayant assez de plafond journalier, triés par frais croissants. */
    public function availableAccounts(float $amount): Collection
    {
        return TransferAccount::query()
            ->where('is_active', true)
            ->whereRaw('daily_limit - current_daily_amount >= ?', [$amount])
            ->with(['operator' => fn ($q) => $q->where('is_active', true)
                ->with(['fees' => fn ($f) => $f->orderBy('min_amount')])])
            ->get()
            ->filter(fn ($a) => $a->operator !== null)
            ->map(fn ($a) => [
                'id' => $a->id,
                'bank_name' => $a->operator->name,
                'account_name' => $a->name,
                'account_number' => $a->account_number,
                'operator_id' => $a->operator_id,
                'operator_logo' => $a->operator->logo,
                'transfer_fee' => $this->computeFee($a->operator, $amount),
                'remaining_daily' => (float) $a->daily_limit - (float) $a->current_daily_amount,
            ])
            ->sortBy('transfer_fee')
            ->values();
    }

    /** À appeler AVANT de créer les commandes (échec = 422, aucune commande créée). */
    public function validateRequest(Request $request): void
    {
        Validator::make($request->all(), [
            'transaction_reference' => ['required', 'string', 'min:4', 'max:100', 'unique:order_transfer_proofs,transaction_reference'],
            'transfer_account_id' => ['required', 'integer', 'exists:transfer_accounts,id'],
            'proof_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ])->validate();
    }

    /** À appeler APRÈS la création des commandes. Remplace les 3 blocs dupliqués d'OrderController. */
    public function recordProofs(array $orderIds, Request $request): array
    {
        return DB::transaction(function () use ($orderIds, $request) {
            $orders = Order::whereIn('id', $orderIds)->get();
            $total = (float) $orders->sum('order_amount');

            $account = TransferAccount::with(['operator.fees'])
                ->where('id', $request->transfer_account_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$account || !$account->operator || !$account->operator->is_active) {
                throw new ManualTransferException(translate('transfer_account_unavailable'), 422);
            }
            if ((float) $account->current_daily_amount + $total > (float) $account->daily_limit) {
                throw new ManualTransferException(translate('transfer_account_limit_reached'), 422);
            }

            $fee = $this->computeFee($account->operator, $total);
            $file = $request->file('proof_image');
            $path = ImageManager::upload('transfer_proof/', strtolower($file->extension() ?: 'png'), $file);
            $group = (string) Str::uuid();

            foreach ($orders->values() as $i => $order) {
                $proof = new OrderTransferProof();
                $proof->forceFill([
                    'order_id' => $order->id,
                    'transfer_group' => $group,
                    'transfer_account_id' => $account->id,
                    'transaction_reference' => $request->transaction_reference,
                    'proof_image' => $path,
                    'expected_amount' => $order->order_amount,
                    'transfer_fee_amount' => $i === 0 ? $fee : 0,
                    'status' => 'pending',
                ])->save();
            }

            $account->increment('current_daily_amount', $total);

            return [
                'transfer_group' => $group,
                'transfer_fee' => $fee,
                'amount_to_transfer' => round($total + $fee, 2),
            ];
        });
    }

    /** Validation d'une preuve par le vendeur (compte vendeur) ou l'admin (compte plateforme). */
    public function reviewProof(OrderTransferProof $proof, string $status, string $reviewerType, int $reviewerId, ?string $reason = null): OrderTransferProof
    {
        return DB::transaction(function () use ($proof, $status, $reviewerType, $reviewerId, $reason) {
            $fresh = OrderTransferProof::whereKey($proof->id)->lockForUpdate()->firstOrFail();
            if ($fresh->status !== 'pending') {
                throw new ManualTransferException(translate('proof_already_reviewed'), 422);
            }

            $fresh->forceFill([
                'status' => $status,
                'reviewed_by' => $reviewerId,
                'reviewed_by_type' => $reviewerType,
                'reviewed_at' => now(),
                'rejection_reason' => $status === 'rejected' ? $reason : null,
            ])->save();

            $order = Order::findOrFail($fresh->order_id);
            if ($status === 'approved') {
                $order->payment_status = 'paid';
                $order->save();
            } else {
                // le plafond du compte est restitué
                TransferAccount::whereKey($fresh->transfer_account_id)->update([
                    'current_daily_amount' => DB::raw('GREATEST(current_daily_amount - ' . (float) $fresh->expected_amount . ', 0)'),
                ]);
            }

            return $fresh;
        });
    }
}
