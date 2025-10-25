<?php

namespace TautId\Shipping\Enums;

enum ShippingTypeEnum: string
{
    case Pickup = 'pickup';
    case Dropoff = 'dropoff';

    public static function toArray(): array
    {
        return collect(self::cases())->pluck('name', 'value')->toArray();
    }
}
