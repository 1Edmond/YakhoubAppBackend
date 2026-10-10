<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Exceptions\ReductionException;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PriceReductionRequest;
use App\Services\PriceReductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceReductionController extends Controller
{
    public function __construct(protected PriceReductionService $reductionService)
    {
    }

    public function getTiers(): JsonResponse
    {
        return response()->json($this->reductionService->getAvailableTiers());
    }

    public function createRequest(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate(['requested_reduction' => ['required', 'numeric', 'gt:0']]);
        // 404 si la commande n'appartient pas au client connecté (fin de l'IDOR)
        $order = Order::where('customer_id', $request->user()->id)->findOrFail($orderId);

        return $this->run(fn () => response()->json(
            $this->reductionService->createRequest($order, $request->user(), (float) $data['requested_reduction']),
            201
        ));
    }

    public function getMyRequests(Request $request): JsonResponse
    {
        return response()->json(PriceReductionRequest::forCustomer($request->user()->id)->latest()->get());
    }

    public function createCartRequest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cart_id' => ['required', 'integer'],
            'requested_reduction' => ['required', 'numeric', 'gt:0'],
        ]);
        $cart = Cart::where('customer_id', $request->user()->id)->findOrFail($data['cart_id']);

        return $this->run(fn () => response()->json(
            $this->reductionService->createCartRequest($cart, $request->user(), (float) $data['requested_reduction']),
            201
        ));
    }

    public function getCartNegotiations(Request $request): JsonResponse
    {
        $requests = PriceReductionRequest::forCustomer($request->user()->id)
            ->whereNotNull('cart_id')->whereNull('order_id')->whereHas('cart')
            ->with(['product', 'vendor'])->latest()->get();

        return response()->json($requests);
    }

    public function respondToCounterOffer(Request $request, int $requestId): JsonResponse
    {
        $request->validate(['accept' => ['required', 'boolean']]);
        $reductionRequest = PriceReductionRequest::forCustomer($request->user()->id)->findOrFail($requestId);

        return $this->run(fn () => response()->json(
            $this->reductionService->respondToCounterOffer($reductionRequest, $request->boolean('accept'))
        ));
    }

    private function run(callable $fn): JsonResponse
    {
        try {
            return $fn();
        } catch (ReductionException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus);
        }
    }
}
