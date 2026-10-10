<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Exceptions\ReductionException;
use App\Http\Controllers\Controller;
use App\Models\PriceReductionRequest;
use App\Services\PriceReductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReductionController extends Controller
{
    public function __construct(protected PriceReductionService $reductionService)
    {
    }

    public function getRequests(Request $request): JsonResponse
    {
        $seller = $request->seller ?? $request->user();
        $requests = PriceReductionRequest::forVendor($seller->id)
            ->with(['customer', 'product', 'order'])->latest()->get();

        return response()->json($requests);
    }

    public function accept(Request $request, int $requestId): JsonResponse
    {
        $req = $this->findOwned($request, $requestId);
        return $this->run(fn () => $this->reductionService->acceptRequest($req));
    }

    public function refuse(Request $request, int $requestId): JsonResponse
    {
        $req = $this->findOwned($request, $requestId);
        return $this->run(fn () => $this->reductionService->refuseRequest($req));
    }

    public function counterOffer(Request $request, int $requestId): JsonResponse
    {
        $data = $request->validate(['counter_amount' => ['required', 'numeric', 'gt:0']]);
        $req = $this->findOwned($request, $requestId);
        return $this->run(fn () => $this->reductionService->counterOffer($req, (float) $data['counter_amount']));
    }

    private function findOwned(Request $request, int $requestId): PriceReductionRequest
    {
        $seller = $request->seller ?? $request->user();
        return PriceReductionRequest::where('vendor_id', $seller->id)->findOrFail($requestId);
    }

    private function run(callable $fn): JsonResponse
    {
        try {
            return response()->json($fn());
        } catch (ReductionException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus);
        }
    }
}
