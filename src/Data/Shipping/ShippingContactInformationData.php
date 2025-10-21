<?php

namespace TautId\Shipping\Data\Shipping;

use Spatie\LaravelData\Data;

class ShippingContactInformationData extends Data
{
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $phone,
        public string $country,
        public string $province,
        public string $city,
        public string $district,
        public string $subdistrict,
        public string $address,
        public int $postal_code,
        public ?float $latitude,
        public ?float $longitude
    )
    {

    }
}
