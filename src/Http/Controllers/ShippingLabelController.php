<?php

namespace TautId\Shipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use TautId\Shipping\Services\ShippingService;
use Illuminate\Database\RecordNotFoundException;

class ShippingLabelController extends Controller
{
    protected ShippingService $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Display the shipping label for printing
     *
     * @param string $trx_id
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function print(string $trx_id)
    {
        try {
            $shippingData = $this->shippingService->getShippingByTrxId($trx_id);

            return view('taut-shipping::shipping.label', [
                'shipping' => $shippingData
            ]);
        } catch (RecordNotFoundException $e) {
            return abort(404);
        }
    }
}
