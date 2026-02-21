<?php

namespace App\Providers;

use App\Services\Payment\AsaasGatewayService;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\StripeGatewayService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class);

        $this->app->bind(StripeGatewayService::class, function () {
            $config = config('payment.gateways.stripe');

            return new StripeGatewayService(
                $config['secret_key'] ?? '',
                $config['webhook_secret'] ?? ''
            );
        });

        $this->app->bind(AsaasGatewayService::class, function () {
            $config = config('payment.gateways.asaas');

            return new AsaasGatewayService(
                $config['api_key'] ?? '',
                $config['webhook_token'] ?? '',
                $config['sandbox'] ?? true
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
