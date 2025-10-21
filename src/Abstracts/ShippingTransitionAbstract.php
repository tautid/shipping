<?php

namespace TautId\Shipping\Abstracts;

use TautId\Shipping\Models\Shipping;

abstract class ShippingTransitionAbstract
{
    abstract public function handle(Shipping $record): void;
}
