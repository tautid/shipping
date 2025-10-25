<?php

namespace TautId\Shipping\Data\Shipping;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class ShippingRequestData extends Data
{
    public function __construct(
        public string $shipping_id,
        public string $type,
        public ?bool $is_use_insurance = false,
        public ?Carbon $pickup_time
    )
    {

    }
}
