<?php

namespace TautId\Shipping\Data\Shipping;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Illuminate\Database\Eloquent\Model;

class UpdateShippingData extends Data
{
    public function __construct(
        public string $id,
        public string $method_id,
        public Model $source,
        public ShippingContactInformationData $origin,
        public ShippingContactInformationData $destination,
        public PackageDimensionData $dimension,
        public float $package_weight,
        public float $package_price,
        public Carbon $date,
        public ?string $note,
        public ?array $meta
    )
    {

    }
}
