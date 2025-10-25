# Taut Shipping

A Laravel package for handling shipping with customizable state transitions and webhook integrations.

## Installation

You can install the package via composer:

```bash
composer require tautid/shipping
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="taut-shipping-config"
```

Publish shippings assets:
```bash
php artisan vendor:publish --tag=taut-shipping-assets
```

Publish the webhook client migrations:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-migrations"
```

Publish the shipping migrations:

```bash
php artisan vendor:publish --tag="taut-shipping-migrations"
```

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="webhook-client-config"
```

Run the migrations:

```bash
php artisan migrate
```

> [!IMPORTANT]
> **Remove the default config in webhook-client after publishing.**
> 
> This step is crucial to ensure proper configuration of the webhook client for your application.

## Available Commands

This package provides two artisan commands:

### 1. Make Transitions Command
Generates custom transition files for handling shipping state changes:

## Customizing Shipping Transitions

You can add custom business logic to shipping state changes by creating your own transition classes. This allows you to:

- Send notifications when shipping are completed
- Update related models when shipping status changes
- Log shipping activities for audit trails
- Integrate with third-party services
- Execute custom business rules

### Creating Custom Transitions

Use the provided command to generate transition files:

```bash
php artisan taut-shipping:make-transitions
```

This will create the following transition files in your `app/Transitions/Shipping/` directory:
- `ToDraft.php` - Executed when shipping is draft
- `ToRequested.php` - Executed when shipping is requested to driver
- `ToDelivering.php` - Executed when shipping becomes delivering
- `ToCanceled.php` - Executed when shipping is canceled
- `ToFailed.php` - Executed when shipping becomes failed
- `ToLost.php` - Executed when shipping becomes lost (package lost)
- `ToReturned.php` - Executed when shipping is returned
- `ToDelivered.php` - Executed when shipping is completed

### Example Custom Transition

Each transition file extends the `ShippingTransitionAbstract` class. Here's an example of customizing the `ToDelivered` transition:

```php
<?php

namespace App\Transitions\Shipping;

use TautId\Shipping\Abstracts\ShippingTransitionAbstract;
use TautId\Shipping\Models\Shipping;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ToDelivered extends ShippingTransitionAbstract
{
    public function handle(Shipping $record): void
    {
        // Your extra step
    }
}
```

### Configuration

The transition namespace can be configured in your `config/taut-shipping.php`:

```php
'transitions_namespace' => 'App\\Transitions\\Shipping',
```

This allows you to organize your transitions in a different namespace if needed.

## Package Features

This package provides comprehensive shipping management functionality through two main services:

### ShippingService Features

The `ShippingService` class provides the following capabilities:

#### Shipping Management
- **Get All Shippings**: Retrieve all shipping records as a data collection
- **Get Paginated Shippings**: Retrieve shipping records with pagination and filtering
- **Get Shipping by ID**: Find a specific shipping record by its ID
- **Get Shipping by Transaction ID**: Find a shipping record by its transaction ID
- **Get Shipping by AWB**: Find a shipping record by its Air Waybill number

#### Shipping Creation and Updates
- **Create Shipping**: Create new shipping records with complete origin/destination information
- **Update Shipping**: Update existing shipping records (only when status is draft)
- **Get Available Methods with Rates**: Get all available shipping methods with their rates for given shipping information

#### Shipping Status Management
- **Change to Requested**: Submit shipping to the shipping provider (requires ShippingRequestData with pickup_time, type, and insurance options)
- **Change to Delivering**: Mark shipping as in transit (from requested status)
- **Change to Delivered**: Mark shipping as successfully delivered (from delivering status) 
- **Change to Canceled**: Cancel a draft shipping (only from draft status)
- **Change to Returned**: Mark shipping as returned to sender (from delivered status)
- **Change to Failed**: Mark shipping as failed during delivery (from requested or delivering status)
- **Change to Lost**: Mark shipping as lost during transit (from delivering status)

#### Label Management
- **Print Label**: Generate a redirect response to print shipping labels via web route
- **Download Label as PDF**: Download shipping label as PDF document using DomPDF

#### Data Management
- **Update Shipping Payload**: Store request payload data
- **Update Shipping Response**: Store response data from shipping providers

### ShippingMethodService Features

The `ShippingMethodService` class provides the following capabilities:

#### Shipping Method Management
- **Get All Shipping Methods**: Retrieve all configured shipping methods
- **Get Paginated Shipping Methods**: Retrieve shipping methods with pagination and filtering
- **Get Shipping Method by ID**: Find a specific shipping method by its ID

#### Driver and Configuration Management
- **Get Available Drivers**: Get list of all available shipping drivers from factory
- **Get Driver Channels**: Get available channels for a specific driver
- **Get Driver Services**: Get available services for a specific driver

#### Shipping Method CRUD Operations
- **Create Shipping Method**: Create new shipping methods with driver configuration and validation
- **Update Shipping Method**: Update existing shipping method configurations with validation
- **Activate Shipping Method**: Enable a shipping method for use
- **Deactivate Shipping Method**: Disable a shipping method

### Usage Examples

#### Basic Shipping Operations

```php
use TautId\Shipping\Services\ShippingService;
use TautId\Shipping\Services\ShippingMethodService;
use TautId\Shipping\Data\Utility\FilterPaginationData;
use TautId\Shipping\Data\Shipping\ShippingInformationData;

// Get shipping service instance
$shippingService = app(ShippingService::class);
$methodService = app(ShippingMethodService::class);

// Get all shippings
$allShippings = $shippingService->getAllShippings();

// Get paginated shippings with filtering
$filterData = FilterPaginationData::from([/* filter data */]);
$paginatedShippings = $shippingService->getPaginateShippings($filterData);

// Find shipping by ID
$shipping = $shippingService->getShippingById('shipping-id');

// Find shipping by transaction ID
$shipping = $shippingService->getShippingByTrxId('SHP-123456');

// Find shipping by AWB
$shipping = $shippingService->getShippingByAwb('AWB-123456');

// Get available shipping methods with rates
$shippingInfo = ShippingInformationData::from([/* shipping info */]);
$availableMethods = $shippingService->getAvailableMethodWithRates($shippingInfo);
```

#### Shipping Status Management

```php
use TautId\Shipping\Data\Shipping\ShippingRequestData;

// Change shipping to requested (requires additional data)
$requestData = ShippingRequestData::from([
    'shipping_id' => 'shipping-id',
    'type' => 'drop-off', // or 'pickup'
    'pickup_time' => '2024-01-01 10:00:00',
    'is_use_insurance' => true
]);
$shippingService->changeShippingToRequested($requestData);

// Other status changes (simple ID-based)
$shippingService->changeShippingToDelivering('shipping-id');
$shippingService->changeShippingToDelivered('shipping-id');
$shippingService->changeShippingToCanceled('shipping-id');
$shippingService->changeShippingToReturned('shipping-id');
$shippingService->changeShippingToFailed('shipping-id');
$shippingService->changeShippingToLost('shipping-id');
```

#### Shipping Method Management

```php
use TautId\Shipping\Data\ShippingMethod\CreateShippingMethodData;
use TautId\Shipping\Data\ShippingMethod\UpdateShippingMethodData;
use TautId\Shipping\Data\Utility\FilterPaginationData;

// Get all shipping methods
$methods = $methodService->getAllShippingMethods();

// Get paginated shipping methods
$filterData = FilterPaginationData::from([/* filter data */]);
$paginatedMethods = $methodService->getPaginateShippingMethods($filterData);

// Get specific shipping method
$method = $methodService->getShippingMethodById('method-id');

// Get available drivers
$drivers = $methodService->getShippingMethodDrivers();

// Get channels for a specific driver
$channels = $methodService->getShippingMethodChannels('driver-name');

// Get services for a specific driver
$services = $methodService->getShippingMethodServices('driver-name');

// Create new shipping method
$createData = CreateShippingMethodData::from([
    'name' => 'Method Name',
    'driver' => 'driver-name',
    'channel' => 'channel-name',
    'service' => 'service-name',
    'is_cod' => false,
    'type' => 'standard',
    'meta' => []
]);
$method = $methodService->createShippingMethod($createData);

// Update shipping method
$updateData = UpdateShippingMethodData::from([
    'id' => 'method-id',
    'name' => 'Updated Method Name',
    // ... other fields
]);
$method = $methodService->updateShippingMethod($updateData);

// Activate/Deactivate shipping method
$methodService->activateShippingMethod('method-id');
$methodService->deactivateShippingMethod('method-id');
```

#### Label Management

```php
// Print shipping label (returns redirect response to web route)
$redirectResponse = $shippingService->printLabel('SHP-123456');

// Download label as PDF (DomPDF with custom paper size)
$pdfResponse = $shippingService->downloadLabelAsPdf('SHP-123456');

### Data Transfer Objects

The package uses Laravel Data for type-safe data transfer objects:

#### Shipping Data Objects
- `ShippingData` - Complete shipping information with all fields
- `CreateShippingData` - Data required to create new shipping records
- `UpdateShippingData` - Data for updating existing shipping records (note: actual class name is `UpdateShippingData`, not `UpdateShippingDataShippingData`)
- `ShippingRequestData` - Data for requesting shipping with pickup/delivery options
- `ShippingInformationData` - Basic shipping information for rate calculation
- `ShippingContactInformationData` - Contact details for origin/destination
- `PackageDimensionData` - Package dimension specifications
- `AvailableShippingWithRateData` - Available methods with pricing information

#### Shipping Method Data Objects
- `ShippingMethodData` - Complete shipping method configuration
- `CreateShippingMethodData` - Data for creating new shipping methods
- `UpdateShippingMethodData` - Data for updating shipping method configurations

#### Utility Data Objects
- `FilterPaginationData` - Pagination and filtering parameters for queries

### Shipping Status Flow

The package supports the following shipping statuses:

- **Created** - Initial status when shipping is first created
- **Draft** - Shipping is prepared but not yet submitted
- **Requested** - Shipping has been submitted to the carrier
- **Delivering** - Package is in transit
- **Delivered** - Package has been successfully delivered
- **Returned** - Package has been returned to sender
- **Canceled** - Shipping has been canceled
- **Failed** - Delivery attempt failed
- **Lost** - Package was lost during transit

#### Status Transition Rules
- Created → Draft (automatic after creation)
- Draft → Requested (via `changeShippingToRequested()`)
- Draft → Canceled (via `changeShippingToCanceled()`)
- Requested → Delivering (via `changeShippingToDelivering()`)
- Requested → Failed (via `changeShippingToFailed()`)
- Delivering → Delivered (via `changeShippingToDelivered()`)
- Delivering → Failed (via `changeShippingToFailed()`)
- Delivering → Lost (via `changeShippingToLost()`)
- Delivered → Returned (via `changeShippingToReturned()`)

### Webhook Integration

The package includes webhook support for real-time shipping status updates from carrier services.

#### Supported Webhooks
- **Apikurir Webhook**: Automatically processes status updates from Apikurir shipping provider

#### Webhook Configuration

The webhook integration uses the `spatie/laravel-webhook-client` package. After publishing the webhook client configuration, configure your webhook endpoints:

```php
// config/webhook-client.php
return [
    'configs' => [
        [
            'name' => 'apikurir',
            'signing_secret' => env('APIKURIR_WEBHOOK_SECRET'),
            'signature_header_name' => 'X-Apikurir-Signature',
            'signature_validator' => TautId\Shipping\Supports\ApikurirSignatureValidator::class,
            'webhook_profile' => Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => TautId\Shipping\Jobs\ApikurirWebhookReceiverJob::class,
        ],
    ],
];
```

#### Webhook Processing

Webhook calls are automatically processed through the `ApikurirWebhookReceiverJob` which:
1. Receives webhook payload from the carrier
2. Validates the webhook signature (implementation needed)
3. Processes the callback through the appropriate shipping driver
4. Updates shipping status accordingly

> [!NOTE]
> The Apikurir signature validator is currently set to always return `true`. You should implement proper signature validation for production use.

## Known Issues & Notes

### Method Naming Inconsistencies
- Some status transition methods have incorrect status assignments in their implementation
- The `UpdateShippingDataShippingData` class name in the service usage should be `UpdateShippingData`

### Status Transition Bugs
The following methods have incorrect status assignments that need fixing:
- `changeShippingToDelivered()` - Sets status to "Requested" instead of "Delivered"
- `changeShippingToCanceled()` - Sets status to "Requested" instead of "Canceled"  
- `changeShippingToFailed()` - Sets status to "Returned" instead of "Failed"
