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
            return new StripeGatewayService();
        });

        $this->app->bind(AsaasGatewayService::class, function () {
            return new AsaasGatewayService();
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
