<?php

namespace TautId\Shipping\Data\Shipping;

use Spatie\LaravelData\Data;

class PackageDimensionData extends Data
{
    public function __construct(
        public ?float $width = 0,
        public ?float $height = 0,
        public ?float $length = 0
    )
    {

    }
}
