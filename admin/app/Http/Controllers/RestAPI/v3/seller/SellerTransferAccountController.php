<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Http\Controllers\Controller;
use App\Models\TransferAccount;
use App\Models\OrderTransferProof;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SellerTransferAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sellerId = $request->user()->id; // Assuming user is the seller
        $accounts = TransferAccount::where('owner_type', 'seller')
            ->where('owner_id', $sellerId)
            ->get();
            
        return response()->json(['accounts' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'operator_id' => 'required|exists:transfer_operators,id',
            'name' => 'required|string',
            'account_number' => 'required|string',
            'daily_limit' => 'required|numeric'
        ]);

        $sellerId = $request->user()->id;

        $account = TransferAccount::create([
            'operator_id' => $request->operator_id,
            'name' => $request->name,
            'account_number' => $request->account_number,
            'daily_limit' => $request->daily_limit,
            'owner_type' => 'seller',
            'owner_id' => $sellerId,
        ]);

        return response()->json(['account' => $account], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $sellerId = $request->user()->id;
        $account = TransferAccount::where('owner_type', 'seller')
            ->where('owner_id', $sellerId)
            ->findOrFail($id);

        $account->update($request->only(['operator_id', 'name', 'account_number', 'daily_limit', 'is_active']));

        return response()->json(['account' => $account]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $sellerId = $request->user()->id;
        $account = TransferAccount::where('owner_type', 'seller')
            ->where('owner_id', $sellerId)
            ->findOrFail($id);

        $account->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function validateTransfer(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        
        $sellerId = $request->user()->id;
        
        $order = Order::where('seller_id', $sellerId)->findOrFail($id);
        $proof = OrderTransferProof::where('order_id', $order->id)->firstOrFail();

        $proof->status = $request->status;
        $proof->save();

        if ($request->status === 'approved') {
            $order->payment_status = 'paid';
            $order->save();
        }

        return response()->json(['message' => 'Status updated', 'proof' => $proof]);
    }
}
