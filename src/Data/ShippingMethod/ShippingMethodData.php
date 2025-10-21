<?php

namespace TautId\Shipping\Data\ShippingMethod;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Shipping\Models\ShippingMethod;

class ShippingMethodData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public string $driver,
        public ?string $channel,
        public ?string $service,
        public bool $is_active,
        public bool $is_cod,
        public ?array $meta,
        public Carbon $created_at
    )
    {

    }

    public static function fromModel(ShippingMethod $record): self
    {
        return new self(
            id: $record->id,
            name: $record->name,
            type: $record->type,
            driver: $record->driver,
            channel: $record->driver_channel,
            service: $record->driver_service,
            is_active: $record->is_active,
            is_cod: $record->is_cod,
            meta: $record->meta,
            created_at: $record->created_at
        );
    }
}
