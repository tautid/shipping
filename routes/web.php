<?php

use Illuminate\Support\Facades\Route;
use TautId\Shipping\Http\Controllers\ShippingLabelController;

Route::webhooks('apikurir/taut-callback', 'apikurir-taut');

Route::get("taut/shipping-label/{trx_id}/print", [ShippingLabelController::class, 'print'])
    ->name('taut.shipping.label.print');
