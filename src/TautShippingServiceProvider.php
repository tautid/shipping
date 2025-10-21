<?php

namespace TautId\Shipping;

use Spatie\LaravelPackageTools\Package;
use TautId\Shipping\Commands\ShippingCommand;
use TautId\Shipping\Commands\MakeTransitionsCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TautId\Shipping\Abstracts\ShippingTransitionAbstract;

class TautShippingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('taut-shipping')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasCommand(MakeTransitionsCommand::class)
            ->hasMigration('create_taut_shippings_table');
    }

    public function boot()
    {
        parent::boot();

        $existing = config('webhook-client.configs', []);
        $mine = require __DIR__.'/../config/shipping-webhook-client.php';

        config([
            'webhook-client.configs' => array_merge($existing, $mine['configs']),
        ]);

        // Publish assets
        $this->publishes([
            __DIR__.'/../assets/images' => public_path('vendor/taut-shipping/images'),
        ], 'taut-shipping-assets');

        $this->registerTransitionBindings();
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/shipping-webhook-client.php',
            'webhook-client'
        );
    }

    public function registerTransitionBindings()
    {
        $namespace = config('taut-shipping.transitions_namespace', 'App\\Transitions\\Shipping');

        $transitions = [
            'ToDraft',
            'ToRequested',
            'ToDelivering',
            'ToDelivered',
            'ToCanceled',
            'ToFailed',
            'ToReturned'
        ];

        foreach ($transitions as $transition) {
            $userClass = "{$namespace}\\{$transition}";
            $packageClass = "TautId\\Shipping\\Transitions\\{$transition}";

            if (class_exists($userClass) && is_subclass_of($userClass, ShippingTransitionAbstract::class)) {
                $this->app->bind($packageClass, $userClass);
            } else {
                $this->app->bind($packageClass, $packageClass);
            }
        }
    }
}
