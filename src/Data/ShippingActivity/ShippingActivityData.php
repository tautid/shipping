<?php

namespace TautId\Shipping\Data\ShippingActivity;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Shipping\Models\ShippingActivity;

class ShippingActivityData extends Data
{
    public function __construct(
        public string $id,
        public string $hash,
        public string $description,
        public Carbon $date
    )
    {

    }

    public static function fromModel(ShippingActivity $record): self
    {
        return new self(
            id: $record->id,
            hash: $record->hash,
            description: $record->description,
            date: $record->date
        );
    }
}
