<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PriceReductionService;
use App\Models\Order;
use App\Models\PriceReductionRequest;

class PriceReductionController extends Controller
{
    protected $reductionService;

    public function __construct(PriceReductionService $reductionService)
    {
        $this->reductionService = $reductionService;
    }

    public function getTiers()
    {
        return response()->json($this->reductionService->getAvailableTiers());
    }

    public function createRequest(Request $request, $orderId)
    {
        $request->validate(['requested_reduction' => 'required|numeric|min:0']);
        $order = Order::findOrFail($orderId);
        $reductionRequest = $this->reductionService->createRequest($order, $request->user(), $request->requested_reduction);
        return response()->json($reductionRequest, 201);
    }

    public function getMyRequests(Request $request)
    {
        $requests = PriceReductionRequest::forCustomer($request->user()->id)->get();
        return response()->json($requests);
    }

    public function createCartRequest(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer',
            'requested_reduction' => 'required|numeric|min:0'
        ]);

        $cart = \App\Models\Cart::where('customer_id', $request->user()->id)
            ->findOrFail($request->cart_id);

        try {
            $reductionRequest = $this->reductionService->createCartRequest($cart, $request->user(), $request->requested_reduction);
            return response()->json($reductionRequest, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getCartNegotiations(Request $request)
    {
        $requests = PriceReductionRequest::forCustomer($request->user()->id)
            ->whereNotNull('cart_id')
            ->whereNull('order_id')
            ->whereHas('cart')
            ->with(['product', 'vendor'])
            ->latest()
            ->get();
        return response()->json($requests);
    }

    public function respondToCounterOffer(Request $request, $requestId)
    {
        $request->validate(['accept' => 'required|boolean']);
        $reductionRequest = PriceReductionRequest::where('customer_id', $request->user()->id)->findOrFail($requestId);
        $result = $this->reductionService->respondToCounterOffer($reductionRequest, $request->accept);
        return response()->json($result);
    }
}
