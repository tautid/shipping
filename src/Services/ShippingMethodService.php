<?php

namespace TautId\Shipping\Services;

use Spatie\LaravelData\DataCollection;
use TautId\Shipping\Models\ShippingMethod;
use TautId\Shipping\Traits\FilterServiceTrait;
use Spatie\LaravelData\PaginatedDataCollection;
use Illuminate\Database\RecordNotFoundException;
use TautId\Shipping\Enums\ShippingMethodTypeEnum;
use TautId\Shipping\Data\Utility\FilterPaginationData;
use TautId\Shipping\Factories\ShippingMethodDriverFactory;
use TautId\Shipping\Data\ShippingMethod\ShippingMethodData;
use TautId\Shipping\Data\ShippingMethod\CreateShippingMethodData;
use TautId\Shipping\Data\ShippingMethod\UpdateShippingMethodData;

class ShippingMethodService
{
    use FilterServiceTrait;

    public function getAllPaymentMethods(): DataCollection
    {
        return new DataCollection(
            ShippingMethodData::class,
            ShippingMethod::get()->map(fn ($record) => ShippingMethodData::from($record))
        );
    }

    public function getPaginatePaymentMethods(FilterPaginationData $data): PaginatedDataCollection
    {
        $query = $this->filteredQuery(ShippingMethod::class, $data);

        $pagination = $query->paginate($data->per_page, ['*'], 'page', $data->page);

        $transformedItems = $pagination->getCollection()->map(fn ($record) => ShippingMethodData::from($record));

        $pagination->setCollection($transformedItems);

        return new PaginatedDataCollection(ShippingMethodData::class, $pagination);
    }

    public function getShippingMethodDrivers(): array
    {
        return ShippingMethodDriverFactory::getOptions();
    }

    public function getShippingMethodChannels(string $driver): array
    {
        $driver = ShippingMethodDriverFactory::getDriver($driver);

        return $driver->channels();
    }

    public function getShippingMethodServices(string $driver): array
    {
        $driver = ShippingMethodDriverFactory::getDriver($driver);

        return $driver->services();
    }

    public function getShippingMethodById(string $method_id): ShippingMethodData
    {
        $record = ShippingMethod::find($method_id);

        if (empty($record))
            throw new RecordNotFoundException('Shipping method not found');

        return ShippingMethodData::from($record);
    }

    public function createPaymentMethod(CreateShippingMethodData $data): ShippingMethodData
    {
        $drivers = ShippingMethodDriverFactory::getOptions();

        if (! in_array(strtolower($data->driver), array_keys($drivers)))
            throw new \InvalidArgumentException('Invalid driver');

        if (! in_array($data->type, array_keys(ShippingMethodTypeEnum::toArray())))
            throw new \InvalidArgumentException('Invalid type');

        $driver = ShippingMethodDriverFactory::getDriver($data->driver);

        if(! in_array($data->channel,array_keys($driver->channels())))
            throw new \InvalidArgumentException('Invalid channel');

        if(! in_array($data->service,array_keys($driver->services())))
            throw new \InvalidArgumentException('Invalid service');

        $driver->metaValidation($data->meta);

        $record = ShippingMethod::create([
            'name' => $data->name,
            'driver' => $data->driver,
            'driver_channel' => $data->channel,
            'is_cod' => $data->is_cod,
            'is_active' => true,
            'meta' => $data->meta
        ]);

        return ShippingMethodData::from($record);
    }

    public function updatePaymentMethod(UpdateShippingMethodData $data): ShippingMethodData
    {
        $record = ShippingMethod::find($data->id);

        if(empty($record))
            throw new RecordNotFoundException('Shipping method not found');

        $drivers = ShippingMethodDriverFactory::getOptions();

        if (! in_array(strtolower($data->driver), array_keys($drivers)))
            throw new \InvalidArgumentException('Invalid driver');

        if (! in_array($data->type, array_keys(ShippingMethodTypeEnum::toArray())))
            throw new \InvalidArgumentException('Invalid type');

        $driver = ShippingMethodDriverFactory::getDriver($data->driver);

        if(! in_array($data->channel, array_keys($driver->channels())))
            throw new \InvalidArgumentException('Invalid channel');

        if(! in_array($data->service, array_keys($driver->services())))
            throw new \InvalidArgumentException('Invalid service');

        $driver->metaValidation($data->meta);

        $record->update([
            'name' => $data->name,
            'driver' => $data->driver,
            'driver_channel' => $data->channel,
            'is_cod' => $data->is_cod,
            'meta' => $data->meta
        ]);

        return ShippingMethodData::from($record);
    }

    public function activateShippingMethod(string $method_id): void
    {
        $record = ShippingMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping method not found');
        }

        $record->update([
            'is_active' => true,
        ]);
    }

    public function deactivateShippingMethod(string $method_id): void
    {
        $record = ShippingMethod::find($method_id);

        if (empty($record)) {
            throw new RecordNotFoundException('Shipping method not found');
        }

        $record->update([
            'is_active' => false,
        ]);
    }
}
