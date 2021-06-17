<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VerifyReceipt
{
    public function getSubscriptionId($subscription, $token)
    {
        //$url = env('APP_V2URL') . "/v2/payments/verify-receipt?token={$token}";
        $url =
            "https://dev.setta.co" .
            "/v2/payments/verify-receipt?token={$token}";

        $response = Http::post($url, [
            'purchaseToken' => $subscription->purchaseToken,
            'productId' => $subscription->subscriptionId,
            'platform' => 'android',
        ]);
        return $response->json()['payload']['orderId'];
    }
}
