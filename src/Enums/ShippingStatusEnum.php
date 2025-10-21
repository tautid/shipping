<?php

namespace TautId\Shipping\Enums;

enum ShippingStatusEnum: string
{
    case Created = 'created';
    case Draft = 'draft';
    case Requested = 'requested';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Canceled = 'canceled';
    case Failed = 'failed';
    case Lost = 'lost';
}
