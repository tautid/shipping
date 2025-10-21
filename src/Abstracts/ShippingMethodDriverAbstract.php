<?php

namespace TautId\Shipping\Abstracts;

use Spatie\LaravelData\DataCollection;
use TautId\Shipping\Data\Shipping\ShippingData;
use TautId\Shipping\Data\Shipping\ShippingInformationData;

abstract class ShippingMethodDriverAbstract
{
    abstract public function channels(): array;

    abstract public function services(): array;

    abstract public function channelImageUrl(string $channel, bool $is_base64 = false): ?string;

    abstract public function getAvailableMethodWithRate(ShippingInformationData $data): DataCollection;

    abstract public function createShipping(ShippingData $data): void;

    abstract public function checkShipping(ShippingData $data): void;

    abstract public function processWebhookCallback(array $data): void;

    abstract public function cancelShipping(ShippingData $data): void;

    abstract public function metaValidation(array $meta): void;
}
