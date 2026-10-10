<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Exceptions\ManualTransferException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransferProof;
use App\Models\TransferAccount;
use App\Models\TransferOperator;
use App\Services\ManualTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerTransferAccountController extends Controller
{
    private function sellerId(Request $request): int
    {
        return (int) (($request->seller ?? $request->user())->id);
    }

    public function operators(): JsonResponse
    {
        return response()->json(TransferOperator::where('is_active', true)->with('fees')->get());
    }

    public function index(Request $request): JsonResponse
    {
        $accounts = TransferAccount::with('operator')
            ->where('owner_type', 'seller')->where('owner_id', $this->sellerId($request))
            ->get()
            ->map(function ($a) {
                $row = $a->toArray();
                $row['operator_name'] = $a->operator->name ?? null; // attendu par TransferAccountModel (Flutter)
                return $row;
            });

        return response()->json(['accounts' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'operator_id' => ['required', 'exists:transfer_operators,id'],
            'name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:50'],
            'daily_limit' => ['required', 'numeric', 'gt:0'],
        ]);

        $account = TransferAccount::create($data + [
            'owner_type' => 'seller',
            'owner_id' => $this->sellerId($request),
            'is_active' => true,
        ]);

        return response()->json(['account' => $account], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'operator_id' => ['sometimes', 'exists:transfer_operators,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'account_number' => ['sometimes', 'string', 'max:50'],
            'daily_limit' => ['sometimes', 'numeric', 'gt:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $account = TransferAccount::where('owner_type', 'seller')
            ->where('owner_id', $this->sellerId($request))->findOrFail($id);
        $account->update($data);

        return response()->json(['account' => $account]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        TransferAccount::where('owner_type', 'seller')
            ->where('owner_id', $this->sellerId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    /** PUT /api/v3/seller/orders/{id}/validate-transfer  { status: approved|rejected, reason? } */
    public function validateTransfer(Request $request, int $id, ManualTransferService $service): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $sellerId = $this->sellerId($request);

        $order = Order::where('seller_id', $sellerId)->where('seller_is', 'seller')->findOrFail($id);
        $proof = OrderTransferProof::where('order_id', $order->id)->latest('id')->firstOrFail();

        // le vendeur ne valide que les preuves versées sur SES comptes
        $ownsAccount = TransferAccount::where('id', $proof->transfer_account_id)
            ->where('owner_type', 'seller')->where('owner_id', $sellerId)->exists();
        if (!$ownsAccount) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $proof = $service->reviewProof($proof, $data['status'], 'seller', $sellerId, $data['reason'] ?? null);
        } catch (ManualTransferException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus);
        }

        return response()->json(['message' => 'Status updated', 'proof' => $proof]);
    }
}
