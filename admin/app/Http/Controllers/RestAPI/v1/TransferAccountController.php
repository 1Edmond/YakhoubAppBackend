<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Services\ManualTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferAccountController extends Controller
{
    /** GET /api/v1/transfer-accounts/available?amount=XXXX  -> { status, accounts:[...], transfer_fee } */
    public function getAvailable(Request $request, ManualTransferService $service): JsonResponse
    {
        $request->validate(['amount' => ['required', 'numeric', 'gt:0']]);
        $accounts = $service->availableAccounts((float) $request->query('amount'));

        return response()->json([
            'status' => $accounts->isEmpty() ? 'empty' : 'success',
            'accounts' => $accounts,
            'transfer_fee' => $accounts->first()['transfer_fee'] ?? 0,
        ]);
    }
}
