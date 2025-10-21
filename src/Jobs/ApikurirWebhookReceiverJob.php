<?php

namespace TautId\Shipping\Jobs;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use TautId\Shipping\Factories\ShippingMethodDriverFactory;

class ApikurirWebhookReceiverJob extends ProcessWebhookJob
{
    public function handle()
    {
        $payload = collect($this->webhookCall['payload'])->toArray();

        ShippingMethodDriverFactory::getDriver('apikurir')
                                    ->processWebhookCallback($payload);
    }
}
