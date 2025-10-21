<?php

namespace TautId\Shipping\Data\Shipping;

use Spatie\LaravelData\Data;

class AvailableShippingWithRateData extends Data
{
    public function __construct(
        public string $method_id,
        public string $method_name,
        public string $method_driver,
        public ?string $method_channel,
        public ?string $method_service,
        public float $shipping_cost,
        public ?string $estimation
    )
    {

    }
}
