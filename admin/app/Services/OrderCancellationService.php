<?php

namespace App\Services;

use App\Events\OrderCancelledByCustomerEvent;
use App\Exceptions\OrderCancellationException;
use App\Models\Order;
use App\Models\User;
use App\Utils\Convert;
use App\Utils\CustomerManager;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\DB;

class OrderCancellationService
{
    public function cancel(User $customer, int $orderId): Order
    {
        $order = DB::transaction(function () use ($customer, $orderId) {
            // Propriété vérifiée DANS la requête : un autre client obtient un 404.
            $order = Order::where('id', $orderId)
                ->where('customer_id', $customer->id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new OrderCancellationException(translate('order_not_found'), 404);
            }
            if (!in_array($order->order_status, config('multishop.cancellable_statuses'), true)) {
                throw new OrderCancellationException(translate('status_not_changeable_now'), 403);
            }

            $fee = in_array($order->order_status, config('multishop.fee_free_statuses'), true)
                ? 0.0
                : (float) config('multishop.cancellation_fee');
            $paid = $order->payment_status === 'paid' ? (float) $order->order_amount : 0.0;

            $this->settleWallet($customer, $order, $fee, $paid);

            OrderManager::getStockUpdateOnOrderStatusChange($order, 'canceled');
            $order->order_status = 'canceled';
            $order->cancellation_fee = $fee;
            $order->save();

            return $order;
        });

        event(new OrderCancelledByCustomerEvent($order));
        return $order;
    }

    /** Remboursement éventuel moins les frais, ou débit des frais seuls. */
    private function settleWallet(User $customer, Order $order, float $fee, float $paid): void
    {
        $net = config('multishop.refund_paid_orders_to_wallet') ? $paid - $fee : -$fee;
        if (abs($net) < 0.005) {
            return;
        }

        if (!$this->walletEnabled()) {
            throw new OrderCancellationException(translate('cancellation_fee_wallet_disabled'), 409);
        }

        $reference = 'order_id_' . $order->id . '_cancellation';

        if ($net > 0) {
            $ok = CustomerManager::create_wallet_transaction($customer->id, $net, 'order_refund', $reference);
        } else {
            $debit = -$net;
            $user = User::whereKey($customer->id)->lockForUpdate()->first();
            if ((float) $user->wallet_balance < (float) Convert::usd($debit)) {
                throw new OrderCancellationException(translate('insufficient_balance_for_cancellation_fee'), 422);
            }
            $ok = CustomerManager::create_wallet_transaction($customer->id, $debit, 'order_cancellation', $reference);
        }

        if ($ok === false) {
            throw new OrderCancellationException(translate('wallet_transaction_failed'), 500);
        }
    }

    private function walletEnabled(): bool
    {
        $setting = \App\Models\BusinessSetting::where('type', 'wallet_status')->first();
        return $setting && (int) $setting->value === 1;
    }
}
