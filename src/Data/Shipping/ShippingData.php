<?php

namespace TautId\Shipping\Data\Shipping;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Shipping\Models\Shipping;
use Illuminate\Database\Eloquent\Model;

class ShippingData extends Data
{
    public function __construct(
        public string $id,
        public string $trx_id,
        public ?string $driver_order_id,
        public Model $source,
        public string $method_name,
        public string $method_driver,
        public string $method_channel,
        public string $method_service,
        public bool $is_cod,
        public bool $is_use_insurance,
        public string $status,
        public ?string $type,
        public ?string $awb,
        public ?string $note,
        public float $package_weight,
        public float $package_price,
        public ?float $shipping_cost,
        public ShippingContactInformationData $origin,
        public ShippingContactInformationData $destination,
        public PackageDimensionData $dimension,
        public Carbon $date,
        public ?Carbon $pickup_time,
        public ?Carbon $delivered_at,
        public ?Carbon $last_check_status_at,
        public ?array $meta,
        public ?array $payload,
        public ?array $response,
        public Carbon $created_at
    )
    {

    }

    public static function fromModel(Shipping $record): self
    {
        return new self(
            id: $record->id,
            trx_id: $record->trx_id,
            driver_order_id: $record->driver_order_id,
            source: $record->source,
            method_name: $record->method_name,
            method_driver: $record->method_driver,
            method_channel: $record->method_channel,
            method_service: $record->method_service,
            is_cod: $record->is_cod,
            is_use_insurance: $record->is_use_insurance ?? false,
            status: $record->status,
            type: $record->type,
            awb: $record->awb,
            note: $record->note,
            package_weight: $record->package_weight,
            package_price: $record->package_price,
            shipping_cost: $record->shipping_cost ?? 0,
            origin: ShippingContactInformationData::from([
                'name' => $record->origin_contact_name,
                'email' => $record->origin_contact_email,
                'phone' => $record->origin_contact_phone,
                'country' => $record->origin_contact_country,
                'province' => $record->origin_contact_province,
                'city' => $record->origin_contact_city,
                'district' => $record->origin_contact_district,
                'subdistrict' => $record->origin_contact_subdistrict,
                'address' => $record->origin_contact_address,
                'postal_code' => $record->origin_contact_postal_code,
                'latitude' => $record->origin_latitude,
                'longitude' => $record->origin_longitude,
            ]),
            destination: ShippingContactInformationData::from([
                'name' => $record->destination_contact_name,
                'email' => $record->destination_contact_email,
                'phone' => $record->destination_contact_phone,
                'country' => $record->destination_contact_country,
                'province' => $record->destination_contact_province,
                'city' => $record->destination_contact_city,
                'district' => $record->destination_contact_district,
                'subdistrict' => $record->destination_contact_subdistrict,
                'address' => $record->destination_contact_address,
                'postal_code' => $record->destination_contact_postal_code,
                'latitude' => $record->destination_latitude,
                'longitude' => $record->destination_longitude,
            ]),
            dimension: PackageDimensionData::from([
                'width' => data_get($record->dimension,'width'),
                'height' => data_get($record->dimension,'height'),
                'length' => data_get($record->dimension,'length')
            ]),
            date: $record->date,
            pickup_time: $record->pickup_time,
            delivered_at: $record->delivered_at,
            last_check_status_at: $record->last_check_status_at,
            meta: $record->meta,
            payload: $record->payload,
            response: $record->response,
            created_at: $record->created_at
        );
    }
}
