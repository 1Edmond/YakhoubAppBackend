<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MobilePaymentService;

class MobilePaymentController extends Controller
{
    protected $paymentService;

    public function __construct(MobilePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiateAirtel(Request $request)
    {
        $request->validate([
            'payment_phone' => 'required|string',
        ]);
        
        \Log::info('Airtel money payment initiated', ['payment_phone' => $request->payment_phone]);
        
        $result = $this->paymentService->initiatePayment($request->all());
        return response()->json($result);
    }

    public function initiateMoov(Request $request)
    {
        $request->validate([
            'payment_phone' => 'required|string',
        ]);
        
        \Log::info('Moov money payment initiated', ['payment_phone' => $request->payment_phone]);
        
        $result = $this->paymentService->initiatePayment($request->all());
        return response()->json($result);
    }

    public function checkStatus($paymentId)
    {
        $result = $this->paymentService->checkPaymentStatus($paymentId);
        return response()->json($result);
    }

    public function webhookAirtel(Request $request)
    {
        return response()->json(['status' => 'received']);
    }

    public function webhookMoov(Request $request)
    {
        return response()->json(['status' => 'received']);
    }
}
