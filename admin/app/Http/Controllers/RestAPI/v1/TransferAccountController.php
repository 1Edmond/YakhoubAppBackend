<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\TransferAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransferAccountController extends Controller
{
    public function getAvailable(Request $request): JsonResponse
    {
        $amount = $request->query('amount', 0);
        
        $account = TransferAccount::where('is_active', true)
            ->whereRaw('current_daily_amount + ? <= daily_limit', [$amount])
            ->with(['operator.fees'])
            ->first();

        if ($account) {
            $feeAmount = 0;
            if ($account->operator) {
                $tier = $account->operator->fees->first(function ($fee) use ($amount) {
                    if ($amount < $fee->min_amount) return false;
                    if ($fee->max_amount !== null && $amount > $fee->max_amount) return false;
                    return true;
                });

                if ($tier) {
                    if ($tier->fee_type === 'percent') {
                        $feeAmount = ($amount * $tier->fee_amount) / 100;
                    } else {
                        $feeAmount = $tier->fee_amount;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'account' => $account,
                'transfer_fee' => $feeAmount,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No available transfer account found.'
        ], 404);
    }
}
