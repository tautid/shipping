<?php

namespace TautId\Shipping\Factories\ShippingMethodDrivers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\DataCollection;
use TautId\Shipping\Models\ShippingMethod;
use TautId\Shipping\Enums\ShippingStatusEnum;
use TautId\Shipping\Services\ShippingService;
use TautId\Shipping\Data\Shipping\ShippingData;
use TautId\Shipping\Helpers\ImageHelper;
use TautId\Shipping\Data\Shipping\ShippingInformationData;
use TautId\Shipping\Abstracts\ShippingMethodDriverAbstract;
use TautId\Shipping\Data\Shipping\AvailableShippingWithRateData;

class ApikurirDriver extends ShippingMethodDriverAbstract
{
    private string $sandbox_url = 'https://sandbox.apikurir.id/';
    private string $production_url = 'https://live.apikurir.id/';

    private function getUsername(): ?string
    {
        return config('taut-shipping.apikurir_username');
    }

    private function getPassword(): ?string
    {
        return config('taut-shipping.apikurir_password');
    }

    private function getUrl(string $endpoint)
    {
        $base_url = (env('APP_ENV','local') == 'production') ? $this->production_url : $this->sandbox_url;

        return "{$base_url}{$endpoint}";
    }

    public function channels(): array
    {
        return [
            'JNE' => 'JNE',
            'SAP Logistic' => 'SAP Logistic',
            'Ninja Xpress' => 'Ninja Xpress',
            'Sicepat' => 'Sicepat',
            'Paxel' => 'Paxel',
            'Lalamove' => 'Lalamove',
            'ID Express' => 'ID Express',
            'Grab' => 'Grab',
            'J&T Express' => 'J&T Express'
        ];
    }

    public function services(): array
    {
        return [
            'Regular' => 'Regular',
            'Express' => 'Express',
            'Same Day' => 'Same Day',
            'Instant' => 'Cargo',
            'Cargo' => 'Cargo'
        ];
    }

    public function channelImageUrl(string $channel, bool $is_base64 = false): ?string
    {
        $image_filename = match(strtolower($channel))
        {
            'jne' => 'jne.png',
            'sap logistic' => 'sap.png',
            'ninja xpress' => 'ninja.png',
            'sicepat' => 'sicepat.png',
            'paxel' => 'paxel.webp',
            'lalamove' => 'lalamove.png',
            'id express' => 'idexpress.png',
            'grab' => 'grab.png',
            'j&t express' => 'jt.png',
            default => null
        };

        if(empty($image_filename)) return null;

        $image_path = public_path("vendor/taut-shipping/images/labels/{$image_filename}");

        if ($is_base64 && file_exists($image_path)) {
            try {
                $grayscale_base64 = ImageHelper::convertImageToGrayscaleBase64($image_path);
                if ($grayscale_base64) {
                    return $grayscale_base64;
                }

                return ImageHelper::convertImageToBase64($image_path);
            } catch (\Exception $e) {
                return asset("vendor/taut-shipping/images/labels/{$image_filename}");
            }
        }

        return asset("vendor/taut-shipping/images/labels/{$image_filename}");
    }

    public function getAvailableMethodWithRate(ShippingInformationData $data): DataCollection
    {
        $available = collect([]);

        $methods = ShippingMethod::where('driver','apikurir')
                                    ->where('is_active',true)
                                    ->get();

        $payload = [
            'isPickup' => true,
            'isCod' => false,
            'dimensions' => [
                $data->dimension->width,
                $data->dimension->height,
                $data->dimension->length
            ],
            'weight' => $data->package_weight,
            'packagePrice' => $data->package_price,
            'origin' => [
                'postalCode' => (string) $data->origin->postal_code,
                'longitude' => $data->origin->longitude,
                'latitude' => $data->origin->latitude
            ],
            'destination' => [
                'postalCode' => (string) $data->destination->postal_code,
                'longitude' => $data->destination->longitude,
                'latitude' => $data->destination->latitude
            ],
            'logistics' => array_values($this->channels()),
            'services' => array_values($this->services())
        ];

        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->post(
                            url:$this->getUrl("shipments/v1/open-api/rates"),
                            data: $payload
                        );

        if(!$response->successful())
            throw new \Exception($response->json('message'));


        foreach($response->collect('data')->toArray() as $items)
        {
            foreach($items as $item)
            {
                $estimation = data_get($item,'minDuration') . " - " . data_get($item,'maxDuration') . " " . data_get($item,'durationType');
                $estimation = (empty(data_get($item,'minDuration')) || empty(data_get($item,'maxDuration'))) ? null : $estimation;
                $available_methods = $methods->where('is_cod',false)
                                                ->where('driver_service',data_get($item,'serviceType'))
                                                ->where('driver_channel',data_get($item,'logisticName'))
                                                ->map(fn($record) =>
                                                    AvailableShippingWithRateData::from([
                                                        'method_id' => (string)$record->id,
                                                        'method_name' => $record->name,
                                                        'method_driver' => $record->driver,
                                                        'method_channel' => $record->driver_channel,
                                                        'method_service' => $record->driver_service,
                                                        'shipping_cost' => (float)data_get($item,'price',0),
                                                        'estimation' => $estimation
                                                    ])
                                                );

                $available = $available->merge($available_methods);
            }
        }

        $payload['isCod'] = true;
        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->post(
                            url:$this->getUrl("shipments/v1/open-api/rates"),
                            data: $payload
                        );

        if(!$response->successful())
            throw new \Exception($response->json('message'));

        foreach($response->collect('data')->toArray() as $items)
        {
            foreach($items as $item)
            {
                $estimation = data_get($item,'minDuration') . " - " . data_get($item,'maxDuration') . " " . data_get($item,'durationType');
                $estimation = (empty(data_get($item,'minDuration')) || empty(data_get($item,'maxDuration'))) ? null : $estimation;

                $available_methods = $methods->where('is_cod',true)
                                                ->where('driver_service',data_get($item,'serviceType'))
                                                ->where('driver_channel',data_get($item,'logisticName'))
                                                ->map(fn($record) =>
                                                    AvailableShippingWithRateData::from([
                                                        'method_id' => (string)$record->id,
                                                        'method_name' => $record->method_name,
                                                        'method_driver' => $record->driver,
                                                        'method_channel' => $record->driver_channel,
                                                        'method_service' => $record->driver_service,
                                                        'shipping_cost' => (float)(data_get($item,'price',0) + data_get($item,'codFee',0)),
                                                        'estimation' => $estimation
                                                    ])
                                                );

                $available = $available->merge($available_methods);
            }
        }

        return new DataCollection(
            AvailableShippingWithRateData::class,
            $available
        );
    }

    public function createShipping(ShippingData $data): void
    {
        $payload = [
            'isPickup' => true,
            'isCod' => $data->is_cod,
            'dimensions' => [
                $data->dimension->width,
                $data->dimension->height,
                $data->dimension->length
            ],
            'weight' => $data->package_weight,
            'packagePrice' => $data->package_price,
            'origin' => [
                'postalCode' => (string) $data->origin->postal_code,
                'longitude' => $data->origin->longitude,
                'latitude' => $data->origin->latitude
            ],
            'destination' => [
                'postalCode' => (string) $data->destination->postal_code,
                'longitude' => $data->destination->longitude,
                'latitude' => $data->destination->latitude
            ],
            'logistics' => [$data->method_channel],
            'services' => [$data->method_service]
        ];

        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->post(
                            url:$this->getUrl("shipments/v1/open-api/rates"),
                            data: $payload
                        );

        if(!$response->successful())
            throw new \Exception('Check rate code failed caused by :: ' . $response->json('message'));

        $service = Str::camel($data->method_service);

        $rate_code = $response->json("data.{$service}.0.rateCode");
        if(empty($rate_code))
            throw new \Exception('Rate code not found');

        $payload = [
            'referenceNumber' => $data->trx_id,
            'isUseInsurance' => false,
            'isPickup' => true,
            'pickupTime' => now()->toISOString(),
            'isCod' => $data->is_cod,
            'shippingNote' => $data->note,
            'rateCode' => 'UDRREG',
            'origin' => [
                'contactName' => $data->origin->name,
                'phone' => $data->origin->phone ?? '',
                'email' => $data->origin->email ?? '',
                'address' => $data->origin->address,
                'addressNote' => null,
                'longitude' => $data->origin->longitude,
                'latidude' => $data->origin->latitude,
                'postalCode' => $data->origin->postal_code,
                'province' => $data->origin->province,
                'city' => $data->origin->city,
                'district' => $data->origin->district,
                'subDistrict' => $data->origin->subdistrict
            ],
            'destination' => [
                'contactName' => $data->destination->name,
                'phone' => $data->destination->phone ?? '',
                'email' => $data->destination->email ?? '',
                'address' => $data->destination->address,
                'addressNote' => null,
                'longitude' => $data->destination->longitude,
                'latidude' => $data->destination->latitude,
                'postalCode' => $data->destination->postal_code,
                'province' => $data->destination->province,
                'city' => $data->destination->city,
                'district' => $data->destination->district,
                'subDistrict' => $data->destination->subdistrict
            ],
            'package' => [
                'qty' => 1,
                'packagePrice' => $data->package_price,
                'description' => null,
                'dimenstions' => [
                    $data->dimension->width,
                    $data->dimension->height,
                    $data->dimension->length
                ],
                'weight' => $data->package_weight
            ]
        ];

        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->post(
                            url: $this->getUrl("orders/v1/open-api/awb"),
                            data: $payload
                        );

        app(ShippingService::class)->updateShippingPayload($data->id,$payload);
        app(ShippingService::class)->updateShippingResponse($data->id, $response->collect()->toArray());

        if(!$response->successful())
            throw new \Exception($response->json('message'));

        ShippingService::make($data->id)
                        ->setAwb($response->json('data.awbNumber'))
                        ->setShippingCost($response->json('data.shipmentPrice'))
                        ->setDriverOrderId($response->json('data.shipmentOrderNumber'))
                        ->setStatus(ShippingStatusEnum::Delivering->value)
                        ->updateDataWithRequestedShippingDriver();
    }

    public function checkShipping(ShippingData $data): void
    {
        if($data->status == ShippingStatusEnum::Draft->value)
            throw new \Exception('unable to check because current status is draft');

        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->get(
                            url: $this->getUrl("shipments/v1/open-api/track/{$data->awb}")
                        );

        if(!$response->successful())
            throw new \Exception($response->json('message'));

        $status = $this->mappingStatus($response->json('data.trackingCode'));

        if($status == $data->status) return;

        ShippingService::make($data->id)
                        ->setStatus($status)
                        ->updateDataWithRequestedShippingDriver();
    }

    public function processWebhookCallback(array $data): void
    {
        $awb = data_get($data,'awbNumber');
        $status = data_get($data,'trackingCode');
        $shipping = app(ShippingService::class)->getShippingByAwb($awb);

        $status = $this->mappingStatus($status);

        if($status == $shipping->status) return;

        ShippingService::make($shipping->id)
                        ->setStatus($status)
                        ->updateDataWithRequestedShippingDriver();
    }

    public function cancelShipping(ShippingData $data): void
    {
        $payload = [
            'orderNumbers' => [$data->driver_order_id]
        ];

        $response = Http::withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->withBasicAuth(
                            username: $this->getUsername(),
                            password: $this->getPassword()
                        )
                        ->put(
                            url: $this->getUrl("shipments/v1/open-api/cancel"),
                            data: $payload
                        );

        if(!$response->successful())
            throw new \Exception($response->json('message'));
    }

    private function mappingStatus(string $status): string
    {
        return match(strtolower($status)){
            'failed to generate awb' => ShippingStatusEnum::Delivering->value,
            'waiting for pickup' => ShippingStatusEnum::Delivering->value,
            'pending pickup/dropoff' => ShippingStatusEnum::Delivering->value,
            'problem pending' => ShippingStatusEnum::Delivering->value,
            'in process' => ShippingStatusEnum::Delivering->value,
            'arrive at destination city' => ShippingStatusEnum::Delivering->value,
            'problem delivery' => ShippingStatusEnum::Delivering->value,
            'undelivery' => ShippingStatusEnum::Delivering->value,
            'rejected' => ShippingStatusEnum::Delivering->value,
            'in process return' => ShippingStatusEnum::Delivering->value,
            'problem return' => ShippingStatusEnum::Delivering,
            'delivered' => ShippingStatusEnum::Delivered->value,
            'cancel' => ShippingStatusEnum::Canceled->value,
            'expired' => ShippingStatusEnum::Failed->value,
            'returned' => ShippingStatusEnum::Returned->value,
            'lost / broken' => ShippingStatusEnum::Lost->value,
            'problem resolved' => ShippingStatusEnum::Delivering->value,
            default => ShippingStatusEnum::Delivering->value
        };
    }

    public function metaValidation(array $meta): void
    {
        //
    }
}
