<?php

namespace TautId\Shipping\Transitions;

use TautId\Shipping\Abstracts\ShippingTransitionAbstract;

class ToReturned extends ShippingTransitionAbstract
{
    public function handle(\TautId\Shipping\Models\Shipping $record): void
    {
        //
    }
}
