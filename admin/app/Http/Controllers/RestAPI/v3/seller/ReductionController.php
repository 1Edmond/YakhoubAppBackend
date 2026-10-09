<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PriceReductionService;
use App\Models\PriceReductionRequest;

class ReductionController extends Controller
{
    protected $reductionService;

    public function __construct(PriceReductionService $reductionService)
    {
        $this->reductionService = $reductionService;
    }

    public function getRequests(Request $request)
    {
        $seller = $request->seller ?? $request->user();
        $requests = PriceReductionRequest::forVendor($seller->id)
            ->with(['customer', 'product', 'order'])
            ->latest()
            ->get();
        return response()->json($requests);
    }

    public function accept(Request $request, $requestId)
    {
        $reductionRequest = $this->findActionableRequest($request, $requestId);
        if (!$reductionRequest instanceof PriceReductionRequest) {
            return $reductionRequest;
        }

        return $this->runAction($reductionRequest, PriceReductionRequest::STATUS_ACCEPTED, function () use ($reductionRequest) {
            return $this->reductionService->acceptRequest($reductionRequest);
        });
    }

    public function refuse(Request $request, $requestId)
    {
        $reductionRequest = $this->findActionableRequest($request, $requestId);
        if (!$reductionRequest instanceof PriceReductionRequest) {
            return $reductionRequest;
        }

        return $this->runAction($reductionRequest, PriceReductionRequest::STATUS_REFUSED, function () use ($reductionRequest) {
            return $this->reductionService->refuseRequest($reductionRequest);
        });
    }

    public function counterOffer(Request $request, $requestId)
    {
        $request->validate(['counter_amount' => 'required|numeric|min:0']);
        $reductionRequest = $this->findActionableRequest($request, $requestId);
        if (!$reductionRequest instanceof PriceReductionRequest) {
            return $reductionRequest;
        }

        if ((int) $reductionRequest->round_number >= 2) {
            return response()->json(['message' => 'Maximum negotiation rounds reached.'], 422);
        }

        return $this->runAction($reductionRequest, PriceReductionRequest::STATUS_COUNTER_OFFER, function () use ($reductionRequest, $request) {
            return $this->reductionService->counterOffer($reductionRequest, $request->counter_amount);
        });
    }

    /**
     * Returns the request if the seller may still act on it (status pending),
     * otherwise a JSON error response.
     */
    private function findActionableRequest(Request $request, $requestId)
    {
        $seller = $request->seller ?? $request->user();
        $reductionRequest = PriceReductionRequest::where('vendor_id', $seller->id)->find($requestId);

        if (!$reductionRequest) {
            return response()->json(['message' => 'Reduction request not found.'], 404);
        }

        if ($reductionRequest->status !== PriceReductionRequest::STATUS_PENDING) {
            return response()->json(['message' => 'This negotiation has already been processed.'], 422);
        }

        return $reductionRequest;
    }

    /**
     * Executes the service action. If a side effect (event / notification) fails
     * after the status was persisted, the action is still reported as successful.
     */
    private function runAction(PriceReductionRequest $reductionRequest, string $expectedStatus, callable $action)
    {
        try {
            $result = $action();
            return response()->json($result);
        } catch (\Throwable $e) {
            $fresh = PriceReductionRequest::find($reductionRequest->id);
            if ($fresh && $fresh->status === $expectedStatus) {
                return response()->json($fresh);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
