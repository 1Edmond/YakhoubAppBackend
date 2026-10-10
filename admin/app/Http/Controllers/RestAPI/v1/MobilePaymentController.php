<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Exceptions\PaymentGatewayNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Services\MobilePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePaymentController extends Controller
{
    public function __construct(protected MobilePaymentService $paymentService)
    {
    }

    public function initiateAirtel(Request $request): JsonResponse
    {
        return $this->initiate($request, 'airtel');
    }

    public function initiateMoov(Request $request): JsonResponse
    {
        return $this->initiate($request, 'moov');
    }

    public function checkStatus(string $paymentId): JsonResponse
    {
        return $this->guard(fn () => $this->paymentService->checkPaymentStatus($paymentId));
    }

    public function webhookAirtel(Request $request): JsonResponse
    {
        return $this->webhook($request, 'airtel');
    }

    public function webhookMoov(Request $request): JsonResponse
    {
        return $this->webhook($request, 'moov');
    }

    private function initiate(Request $request, string $gateway): JsonResponse
    {
        $data = $request->validate([
            'payment_phone' => ['required', 'string', 'max:20'],
            'order_id' => ['nullable', 'integer'],
        ]);

        if (!config("multishop.payments.$gateway.enabled")) {
            return $this->notConfigured($gateway);
        }

        return $this->guard(fn () => $this->paymentService->initiatePayment($data + ['gateway' => $gateway]));
    }

    private function webhook(Request $request, string $gateway): JsonResponse
    {
        $secret = config("multishop.payments.$gateway.webhook_secret");
        if (!config("multishop.payments.$gateway.enabled") || !$secret) {
            return $this->notConfigured($gateway);
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($expected, (string) $request->header('X-Signature', ''))) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        \Log::info("Webhook {$gateway} accepted", ['payload' => $request->all()]);
        // TODO (clés réelles) : rapprocher le paiement, mettre à jour la commande, journaliser (CDC 3.5.3).
        return response()->json(['status' => 'received'], 202);
    }

    private function guard(callable $fn): JsonResponse
    {
        try {
            return response()->json($fn());
        } catch (PaymentGatewayNotConfiguredException $e) {
            return $this->notConfigured($e->gateway);
        }
    }

    private function notConfigured(string $gateway): JsonResponse
    {
        return response()->json([
            'code' => 'payment_gateway_not_configured',
            'gateway' => $gateway,
            'message' => translate('payment_gateway_not_configured'),
        ], 503);
    }
}
