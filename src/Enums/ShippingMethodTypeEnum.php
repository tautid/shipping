<?php

namespace TautId\Shipping\Enums;

enum ShippingMethodTypeEnum: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';
}
