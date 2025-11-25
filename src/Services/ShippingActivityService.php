<?php

namespace TautId\Shipping\Services;

use Spatie\LaravelData\DataCollection;
use TautId\Shipping\Models\ShippingActivity;
use TautId\Shipping\Data\ShippingActivity\ShippingActivityData;
use TautId\Shipping\Data\ShippingActivity\CreateShippingActivityData;

class ShippingActivityService
{
    public function getActivitiesByShippingId(string $shipping_id): DataCollection
    {
        return new DataCollection(
            ShippingActivityData::class,
            ShippingActivity::where('shipping_id',$shipping_id)
                                    ->orderBy('date','desc')
                                    ->get()
                                    ->map(fn($item) => ShippingActivityData::from($item))
        );
    }

    public function createActivity(CreateShippingActivityData $data): ShippingActivityData
    {
        $record = ShippingActivity::updateOrCreate([
            'shipping_id' => $data->shipping_id,
            'hash' => $data->hash
        ],[
            'description' => $data->description,
            'date' => $data->date
        ]);

        return ShippingActivityData::from($record);
    }
}
