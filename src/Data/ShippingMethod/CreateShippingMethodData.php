<?php

namespace TautId\Shipping\Data\ShippingMethod;

use Spatie\LaravelData\Data;

class CreateShippingMethodData extends Data
{
    public function __construct(
        public string $name,
        public string $driver,
        public string $channel,
        public string $service,
        public string $type,
        public bool $is_cod = false,
        public ?array $meta = []
    )
    {

    }
}
