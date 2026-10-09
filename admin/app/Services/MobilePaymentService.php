<?php

namespace App\Services;

interface MobilePaymentInterface
{
    public function initiatePayment(array $data);
    public function checkPaymentStatus($paymentId);
    public function processRefund($paymentId);
}

class MobilePaymentService implements MobilePaymentInterface
{
    public function initiatePayment(array $data)
    {
        // Mock implementation
        sleep(1);
        return ['status' => 'success', 'transaction_id' => uniqid()];
    }

    public function checkPaymentStatus($paymentId)
    {
        return ['status' => 'completed'];
    }

    public function processRefund($paymentId)
    {
        return ['status' => 'refunded'];
    }
}
