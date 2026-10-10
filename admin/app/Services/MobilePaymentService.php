<?php

namespace App\Services;

use App\Exceptions\PaymentGatewayNotConfiguredException;

interface MobilePaymentInterface
{
    public function initiatePayment(array $data);
    public function checkPaymentStatus($paymentId);
    public function processRefund($paymentId);
}

/**
 * Passerelle API Airtel / Moov : NON branchée (pas de clés API).
 * Elle échoue explicitement : jamais de faux succès.
 * Le jour où les clés existent : implémenter ces 3 méthodes, rien d'autre à changer.
 */
class MobilePaymentService implements MobilePaymentInterface
{
    public function initiatePayment(array $data)
    {
        throw new PaymentGatewayNotConfiguredException($data['gateway'] ?? 'mobile_money');
    }

    public function checkPaymentStatus($paymentId)
    {
        throw new PaymentGatewayNotConfiguredException('mobile_money');
    }

    public function processRefund($paymentId)
    {
        throw new PaymentGatewayNotConfiguredException('mobile_money');
    }
}
