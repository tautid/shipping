<?php

namespace TautId\Shipping\Transitions;

use TautId\Shipping\Abstracts\ShippingTransitionAbstract;

class ToRequested extends ShippingTransitionAbstract
{
    public function handle(\TautId\Shipping\Models\Shipping $record): void
    {
        //
    }
}
