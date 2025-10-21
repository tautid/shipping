<?php

namespace TautId\Shipping\Data\Shipping;

use Spatie\LaravelData\Data;

class ShippingInformationData extends Data
{
    public function __construct(
        public ShippingContactInformationData $origin,
        public ShippingContactInformationData $destination,
        public PackageDimensionData $dimension,
        public float $package_weight,
        public float $package_price
    )
    {

    }
}
