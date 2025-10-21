<?php

return [
    'configs' => [
        [
            'name' => 'apikurir-taut',
            'signing_secret' => env('APIKURIR_SECRET'),
            'signature_header_name' => 'signature',
            'signature_validator' => \TautId\Shipping\Supports\ApikurirSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \TautId\Shipping\Jobs\ApikurirWebhookReceiverJob::class,
        ],
    ]
];
