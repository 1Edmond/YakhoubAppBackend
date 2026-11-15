<?php

namespace App\Listeners;

use App\Events\ReductionRespondedEvent;
use App\Models\User;
use App\Traits\PushNotificationTrait;

class NotifyCustomerOfReductionResponse
{
    use PushNotificationTrait;

    public function handle(ReductionRespondedEvent $event): void
    {
        $request = $event->request;
        if (!$request) {
            return;
        }

        $customer = $request->customer;
        if (!$customer && !empty($request->customer_id)) {
            $customer = User::find($request->customer_id);
        }

        $fcmToken = $customer?->cm_firebase_token;
        if ($fcmToken) {
            $statusText = match ($event->status) {
                'accepted' => translate('Votre offre de négociation a été acceptée !'),
                'refused' => translate('Votre offre de négociation a été refusée.'),
                'counter_offer' => translate('Le vendeur vous a fait une contre-offre pour votre panier.'),
                default => translate('Mise à jour de votre demande de négociation.')
            };

            $postData = [
                'title' => translate('Réponse à votre négociation'),
                'description' => $statusText,
                'image' => '',
                'order_id' => '',
                'type' => 'reduction_response',
                'request_id' => (string)$request->id,
                'status' => (string)$event->status,
            ];
            $this->sendPushNotificationToDevice($fcmToken, $postData);
        }
    }
}
