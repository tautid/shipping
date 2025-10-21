<?php

namespace TautId\Shipping\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TautId\Shipping\Shipping
 */
class TautShipping extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TautId\Shipping\TautShipping::class;
    }
}
