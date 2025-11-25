<?php

namespace TautId\Shipping\Data\ShippingActivity;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CreateShippingActivityData extends Data
{
    public function __construct(
        public string $shipping_id,
        public string $hash,
        public string $description,
        public Carbon $date
    )
    {

    }
}
