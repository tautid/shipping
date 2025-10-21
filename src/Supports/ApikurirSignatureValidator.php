<?php

namespace TautId\Shipping\Supports;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class ApikurirSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        // TODO: Do validator webhook from apikurir
        return true;

        // $signature = $request->header($config->signatureHeaderName);

        // if (! $signature) {
        //     return false;
        // }

        // $signingSecret = config('taut-payment.moota_transaction_webhook_secret');

        // if (empty($signingSecret)) {
        //     throw InvalidConfig::signingSecretNotSet();
        // }

        // $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        // return hash_equals($computedSignature, $signature);
    }
}
