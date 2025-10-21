<?php

namespace TautId\Shipping\Factories;

use TautId\Shipping\Abstracts\ShippingMethodDriverAbstract;
use TautId\Shipping\Factories\ShippingMethodDrivers\ApikurirDriver;

class ShippingMethodDriverFactory
{
    public static function getDriver(string $driverName): ShippingMethodDriverAbstract
    {
        $driver = match (strtolower($driverName)) {
            'apikurir' => new ApikurirDriver,
            default => null
        };

        if (empty($driver)) {
            throw new \Exception('Driver not found');
        }

        if (! in_array(strtolower($driverName), config('taut-shipping.drivers'))) {
            throw new \Exception("{$driverName} is disabled from config");
        }

        return $driver;
    }

    public static function getOptions(): array
    {
        $options = collect(config('taut-shipping.drivers'))
            ->mapWithKeys(fn ($item) => [$item => ucfirst($item)])
            ->toArray();

        return $options;
    }
}
