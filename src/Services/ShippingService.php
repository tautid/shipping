<?php

namespace TautId\Shipping\Services;

use TautId\Shipping\Models\Shipping;
use Illuminate\Http\RedirectResponse;
use Spatie\LaravelData\DataCollection;
use TautId\Shipping\Enums\ShippingTypeEnum;
use TautId\Shipping\Enums\ShippingStatusEnum;
use TautId\Shipping\Traits\FilterServiceTrait;
use Spatie\LaravelData\PaginatedDataCollection;
use TautId\Shipping\Data\Shipping\ShippingData;
use Illuminate\Database\RecordNotFoundException;
use TautId\Shipping\Data\Shipping\CreateShippingData;
use TautId\Shipping\Data\Shipping\ShippingRequestData;
use TautId\Shipping\Data\Utility\FilterPaginationData;
use TautId\Shipping\Data\Shipping\ShippingInformationData;
use TautId\Shipping\Factories\ShippingMethodDriverFactory;
use TautId\Shipping\Data\Shipping\AvailableShippingWithRateData;
use TautId\Shipping\Data\Shipping\UpdateShippingData;

class ShippingService
{
    use FilterServiceTrait;

    private static ?string $shipping_id;
    private static ?string $awb;
    private static ?float $shipping_cost;
    private static ?float $insurance_cost;
    private static ?string $driver_order_id;
    private static ?string $status;

    public function getAllShippings(): DataCollection
    {
        return new DataCollection(
            ShippingData::class,
            Shipping::get()->map(fn ($record) => ShippingData::from($record))
        );
    }

    public function getPaginateShippings(FilterPaginationData $data): PaginatedDataCollection
    {
        $query = $this->filteredQuery(Shipping::class, $data);

        $pagination = $query->paginate($data->per_page, ['*'], 'page', $data->page);

        $transformedItems = $pagination->getCollection()->map(fn ($record) => ShippingData::from($record));

        $pagination->setCollection($transformedItems);

        return new PaginatedDataCollection(ShippingData::class, $pagination);
    }

    public function getShippingById(string $shipping_id): ShippingData
    {
        $record = Shipping::find($shipping_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        return ShippingData::from($record);
    }

    public function getShippingByTrxId(string $shipping_trx_id): ShippingData
    {
        $record = Shipping::where('trx_id',$shipping_trx_id)->first();

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        return ShippingData::from($record);
    }

    public function getShippingByAwb(string $shipping_awb): ShippingData
    {
        $record = Shipping::where('awb',$shipping_awb)->first();

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        return ShippingData::from($record);
    }

    public function getAvailableMethodWithRates(ShippingInformationData $data): DataCollection
    {
        $options = new DataCollection(AvailableShippingWithRateData::class,collect([]));
        $drivers = ShippingMethodDriverFactory::getOptions();

        foreach($drivers as $driver)
        {
            try{
                $available = ShippingMethodDriverFactory::getDriver($driver)->getAvailableMethodWithRate($data);
                $merged = $options->toCollection()->merge($available->toCollection());

                $options = new DataCollection(AvailableShippingWithRateData::class,$merged);
            }catch(\Exception $e){
                continue;
            }
        }

        return $options;
    }

    public function createShipping(CreateShippingData $data): ShippingData
    {
        $method = app(ShippingMethodService::class)->getShippingMethodById($data->method_id);

        $origin = $data->origin;
        $destination = $data->destination;
        $origin_full_address = "$origin->address, $origin->subdistrict, $origin->district, $origin->city, $origin->province, $origin->country ($origin->postal_code)";
        $destination_full_address = "$destination->address, $destination->subdistrict, $destination->district, $destination->city, $destination->province, $destination->country ($destination->postal_code)";


        $record = Shipping::create([
            'trx_id' => uniqid('SHP-'),
            'method_id' => $method->id,
            'source_id' => $data->source->id,
            'source_type' => get_class($data->source),
            'method_name' => $method->name,
            'method_driver' => $method->driver,
            'method_channel' => $method->channel,
            'method_service' => $method->service,
            'is_cod' => $method->is_cod,
            'status' => ShippingStatusEnum::Created->value,
            'note' => $data->note,
            'package_weight' => $data->package_weight,
            'package_price' => $data->package_price,
            'origin_contact_name' => $data->origin->name,
            'origin_contact_email' => $data->origin->email,
            'origin_contact_phone' => $data->origin->phone,
            'origin_contact_country' => $data->origin->country,
            'origin_contact_province' => $data->origin->province,
            'origin_contact_city' => $data->origin->city,
            'origin_contact_district' => $data->origin->district,
            'origin_contact_subdistrict' => $data->origin->subdistrict,
            'origin_contact_address' => $data->origin->address,
            'origin_contact_postal_code' => $data->origin->postal_code,
            'origin_contact_full_adress' => $origin_full_address,
            'origin_latitude' => $data->origin->latitude,
            'origin_longitude' => $data->origin->longitude,
            'destination_contact_name' => $data->destination->name,
            'destination_contact_email' => $data->destination->email,
            'destination_contact_phone' => $data->destination->phone,
            'destination_contact_country' => $data->destination->country,
            'destination_contact_province' => $data->destination->province,
            'destination_contact_city' => $data->destination->city,
            'destination_contact_district' => $data->destination->district,
            'destination_contact_subdistrict' => $data->destination->subdistrict,
            'destination_contact_address' => $data->destination->address,
            'destination_contact_postal_code' => $data->destination->postal_code,
            'destination_contact_full_address' => $destination_full_address,
            'destination_latitude' => $data->destination->latitude,
            'destination_longitude' => $data->destination->longitude,
            'date' => $data->date,
            'meta' => $data->meta,
            'dimension' => [
                'width' => $data->dimension->width,
                'height' => $data->dimension->height,
                'length' => $data->dimension->length
            ]
        ]);

        $record->update([
            'status' => ShippingStatusEnum::Draft->value
        ]);

        return ShippingData::from($record);
    }

    public function updateShipping(UpdateShippingData $data): ShippingData
    {
        $record = Shipping::find($data->id);

        if(empty($record))
            throw new RecordNotFoundException('Shipping not found');

        if($record->status != ShippingStatusEnum::Draft->value)
            throw new \Exception('Unable to update shipping when current status is not draft');

        $method = app(ShippingMethodService::class)->getShippingMethodById($data->method_id);

        $origin = $data->origin;
        $destination = $data->destination;
        $origin_full_address = "$origin->address, $origin->subdistrict, $origin->district, $origin->city, $origin->province, $origin->country ($origin->postal_code)";
        $destination_full_address = "$destination->address, $destination->subdistrict, $destination->district, $destination->city, $destination->province, $destination->country ($destination->postal_code)";

        $record->update([
            'method_id' => $method->id,
            'source_id' => $data->source->id,
            'source_type' => get_class($data->source),
            'method_name' => $method->name,
            'method_driver' => $method->driver,
            'method_channel' => $method->channel,
            'method_service' => $method->service,
            'is_cod' => $method->is_cod,
            'note' => $data->note,
            'package_weight' => $data->package_weight,
            'package_price' => $data->package_price,
            'origin_contact_name' => $data->origin->name,
            'origin_contact_email' => $data->origin->email,
            'origin_contact_phone' => $data->origin->phone,
            'origin_contact_country' => $data->origin->country,
            'origin_contact_province' => $data->origin->province,
            'origin_contact_city' => $data->origin->city,
            'origin_contact_district' => $data->origin->district,
            'origin_contact_subdistrict' => $data->origin->subdistrict,
            'origin_contact_address' => $data->origin->address,
            'origin_contact_full_adress' => $origin_full_address,
            'origin_contact_postal_code' => $data->origin->postal_code,
            'origin_latitude' => $data->origin->latitude,
            'origin_longitude' => $data->origin->longitude,
            'destination_contact_name' => $data->destination->name,
            'destination_contact_email' => $data->destination->email,
            'destination_contact_phone' => $data->destination->phone,
            'destination_contact_country' => $data->destination->country,
            'destination_contact_province' => $data->destination->province,
            'destination_contact_city' => $data->destination->city,
            'destination_contact_district' => $data->destination->district,
            'destination_contact_subdistrict' => $data->destination->subdistrict,
            'destination_contact_address' => $data->destination->address,
            'destination_contact_postal_code' => $data->destination->postal_code,
            'destination_contact_full_address' => $destination_full_address,
            'destination_latitude' => $data->destination->latitude,
            'destination_longitude' => $data->destination->longitude,
            'date' => $data->date,
            'meta' => $data->meta,
            'dimension' => [
                'width' => $data->dimension->width,
                'height' => $data->dimension->height,
                'length' => $data->dimension->length
            ]
        ]);

        return ShippingData::from($record);
    }

    public function changeShippingToRequested(ShippingRequestData $data): void
    {
        if(!in_array($data->type,array_keys(ShippingTypeEnum::toArray())))
            throw new \InvalidArgumentException('Type is invalid from ShippingTypeEnum');

        $record = Shipping::find($data->shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if ($record->status != ShippingStatusEnum::Draft->value) {
            throw new \InvalidArgumentException('This current payment status is not draft');
        }

        $create_shipping_data = ShippingData::from($record);
        $create_shipping_data->pickup_time = $data->pickup_time;
        $create_shipping_data->type = $data->type;
        $create_shipping_data->is_use_insurance = $data->is_use_insurance ?? false;

        ShippingMethodDriverFactory::getDriver($record->method_driver)
                            ->createShipping($create_shipping_data);

        $record->update([
            'status' => ShippingStatusEnum::Requested->value,
            'type' => $data->type,
            'pickup_time' => $data->pickup_time,
            'is_use_insurance' => $data->is_use_insurance ?? false
        ]);
    }

    public function changeShippingToDelivering(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if ($record->status != ShippingStatusEnum::Requested->value) {
            throw new \InvalidArgumentException('This current shipping status is not requested');
        }

        $record->update([
            'status' => ShippingStatusEnum::Delivering->value,
        ]);
    }

    public function changeShippingToDelivered(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if ($record->status != ShippingStatusEnum::Delivering->value) {
            throw new \InvalidArgumentException('This current shipping status is not delivering');
        }

        $record->update([
            'status' => ShippingStatusEnum::Delivered->value,
        ]);
    }

    public function changeShippingToCanceled(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if ($record->status != ShippingStatusEnum::Draft->value) {
            throw new \InvalidArgumentException('This current shipping status is not delivering');
        }

        $record->update([
            'status' => ShippingStatusEnum::Canceled->value,
        ]);
    }

    public function changeShippingToReturned(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if ($record->status != ShippingStatusEnum::Delivered->value) {
            throw new \InvalidArgumentException('This current shipping status is not delivered');
        }

        $record->update([
            'status' => ShippingStatusEnum::Returned->value,
        ]);
    }

    public function changeShippingToFailed(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping not found');
        }

        if (!in_array($record->status,[ShippingStatusEnum::Delivering->value,ShippingStatusEnum::Requested->value])) {
            throw new \InvalidArgumentException('This current shipping status is not requested or delivering');
        }

        $record->update([
            'status' => ShippingStatusEnum::Failed->value,
        ]);
    }

    public function changeShippingToLost(string $shipping_id): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        if ($record->status != ShippingStatusEnum::Delivering->value)
            throw new \InvalidArgumentException('This current shipping status is not delivering');

        $record->update([
            'status' => ShippingStatusEnum::Lost->value,
        ]);
    }

    public function updateShippingPayload(string $shipping_id, array $payload): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        $record->update([
            'payload' => $payload,
        ]);
    }

    public function updateShippingResponse(string $shipping_id, array $response): void
    {
        $record = Shipping::find($shipping_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        $record->update([
            'response' => $response,
        ]);
    }

    public static function make(string $shipping_id): self
    {
        self::$shipping_id = $shipping_id;

        return new self();
    }

    public function setAwb(?string $awb): self
    {
        self::$awb = $awb;

        return $this;
    }

    public function setShippingCost(float $cost): self
    {
        self::$shipping_cost = $cost;

        return $this;
    }

    public function setInsuranceCost(float $cost): self
    {
        self::$insurance_cost = $cost;

        return $this;
    }

    public function setDriverOrderId(string $driver_order_id): self
    {
        self::$driver_order_id = $driver_order_id;

        return $this;
    }

    public function setStatus(string $status): self
    {
        self::$status = $status;

        return $this;
    }

    public function updateDataWithRequestedShippingDriver(): void
    {
        $record = Shipping::find(self::$shipping_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping not found');

        $payload = [
            'awb' => self::$awb ?? null,
            'shipping_cost' => self::$shipping_cost ?? null,
            'insurance_cost' => self::$insurance_cost ?? null,
            'total_cost' => (empty(self::$shipping_cost)) ? (self::$shipping_cost + self::$insurance_cost ?? 0) : null,
            'driver_order_id' => self::$driver_order_id ?? null,
            'status' => self::$status ?? null
        ];

        foreach($payload as $key => $value)
        {
            if(empty($value))
            {
                unset($payload[$key]);
            }
        }

        $record->update($payload);
    }

    public function printLabel(string $trx_id): RedirectResponse
    {
        return redirect()->to(route("taut.shipping.label.print",['trx_id' => $trx_id]));
    }

    public function downloadLabelAsPdf(string $trx_id): \Illuminate\Http\Response
    {
        $shipping = $this->getShippingByTrxId($trx_id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('taut-shipping::shipping.label-pdf', compact('shipping'));
        $pdf->setPaper([0, 0, 283.46, 283.46], 'portrait');

        // Basic DomPDF options
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('defaultMediaType', 'print');

        return $pdf->download("shipping-label-{$shipping->trx_id}.pdf");
    }
}
