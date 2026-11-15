<?php

namespace App\Listeners;

use App\Events\ReductionRequestedEvent;
use App\Models\Seller;
use App\Traits\PushNotificationTrait;

class NotifyVendorOfReductionRequest
{
    use PushNotificationTrait;

    public function handle(ReductionRequestedEvent $event): void
    {
        $request = $event->request;
        if (!$request) {
            return;
        }

        $vendor = $request->vendor ?? $request->seller;
        if (!$vendor && !empty($request->vendor_id)) {
            $vendor = Seller::find($request->vendor_id);
        }

        $fcmToken = $vendor?->cm_firebase_token;
        if ($fcmToken) {
            $postData = [
                'title' => translate('Nouvelle demande de négociation'),
                'description' => translate('Un client a proposé un prix négocié pour son panier'),
                'image' => '',
                'order_id' => '',
                'type' => 'reduction_request',
                'request_id' => (string)$request->id,
            ];
            $this->sendPushNotificationToDevice($fcmToken, $postData);
        }
    }
}
