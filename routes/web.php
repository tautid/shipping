<?php

use Illuminate\Support\Facades\Route;
use TautId\Shipping\Http\Controllers\ShippingLabelController;


Route::get("taut/shipping-label/{trx_id}/print", [ShippingLabelController::class, 'print'])
    ->name('taut.shipping.label.print');

Route::webhooks(config('taut-shipping.apikurir_callback_endpoint') ?? 'apikurir/taut-callback', 'apikurir-taut');
