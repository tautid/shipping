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
- **Change to Requested**: Submit shipping to the shipping provider
- **Change to Delivering**: Mark shipping as in transit
- **Change to Delivered**: Mark shipping as successfully delivered
- **Change to Canceled**: Cancel a draft shipping
- **Change to Returned**: Mark shipping as returned to sender
- **Change to Failed**: Mark shipping as failed during delivery
- **Change to Lost**: Mark shipping as lost during transit

#### Label Management
- **Print Label**: Generate a redirect response to print shipping labels
- **Download Label as PDF**: Download shipping label as PDF document

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
- **Get Available Drivers**: Get list of all available shipping drivers
- **Get Driver Channels**: Get available channels for a specific driver
- **Get Driver Services**: Get available services for a specific driver

#### Shipping Method CRUD Operations
- **Create Shipping Method**: Create new shipping methods with driver configuration
- **Update Shipping Method**: Update existing shipping method configurations
- **Activate Shipping Method**: Enable a shipping method for use
- **Deactivate Shipping Method**: Disable a shipping method

### Usage Examples

#### Basic Shipping Operations

```php
use TautId\Shipping\Services\ShippingService;
use TautId\Shipping\Services\ShippingMethodService;

// Get shipping service instance
$shippingService = app(ShippingService::class);
$methodService = app(ShippingMethodService::class);

// Get all shippings
$allShippings = $shippingService->getAllShippings();

// Get paginated shippings with filtering
$paginatedShippings = $shippingService->getPaginateShippings($filterData);

// Find shipping by ID
$shipping = $shippingService->getShippingById('shipping-id');

// Find shipping by transaction ID
$shipping = $shippingService->getShippingByTrxId('SHP-123456');

// Get available shipping methods with rates
$availableMethods = $shippingService->getAvailableMethodWithRates($shippingInfo);
```

#### Shipping Status Management

```php
// Change shipping status
$shippingService->changeShippingToRequested('shipping-id');
$shippingService->changeShippingToDelivering('shipping-id');
$shippingService->changeShippingToDelivered('shipping-id');
$shippingService->changeShippingToCanceled('shipping-id');
$shippingService->changeShippingToReturned('shipping-id');
$shippingService->changeShippingToFailed('shipping-id');
$shippingService->changeShippingToLost('shipping-id');
```

#### Shipping Method Management

```php
// Get all shipping methods
$methods = $methodService->getAllPaymentMethods();

// Get available drivers
$drivers = $methodService->getShippingMethodDrivers();

// Get channels for a specific driver
$channels = $methodService->getShippingMethodChannels('driver-name');

// Get services for a specific driver
$services = $methodService->getShippingMethodServices('driver-name');

// Create new shipping method
$method = $methodService->createPaymentMethod($createData);

// Update shipping method
$method = $methodService->updatePaymentMethod($updateData);

// Activate/Deactivate shipping method
$methodService->activateShippingMethod('method-id');
$methodService->deactivateShippingMethod('method-id');
```

#### Label Management

```php
// Print shipping label (returns redirect response)
$redirectResponse = $shippingService->printLabel('SHP-123456');

// Download label as PDF
$pdfResponse = $shippingService->downloadLabelAsPdf('SHP-123456');
```

### Data Transfer Objects

The package uses Laravel Data for type-safe data transfer objects:

- `ShippingData` - Complete shipping information
- `CreateShippingData` - Data required to create new shipping
- `UpdateShippingDataShippingData` - Data for updating shipping
- `ShippingInformationData` - Basic shipping information for rate calculation
- `AvailableShippingWithRateData` - Available methods with pricing
- `ShippingMethodData` - Shipping method configuration
- `CreateShippingMethodData` - Data for creating shipping methods
- `UpdateShippingMethodData` - Data for updating shipping methods
- `FilterPaginationData` - Pagination and filtering parameters
