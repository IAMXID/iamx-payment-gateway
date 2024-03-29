<?php

namespace IAMXID\IamxPaymentGateway;

use IAMXID\IamxPaymentGateway\Commands\CheckTokenPayment;
use IAMXID\IamxPaymentGateway\Commands\ClearOldOpenPayments;
use IAMXID\IamxPaymentGateway\View\Components\PaymentGatewayConnector;
use Illuminate\Support\ServiceProvider;

class IamxPaymentGatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        $this->app->make('config')->set('logging.channels.paymentGateway', [
            'driver' => 'daily',
            'path' => storage_path('logs/paymentGateway.log'),
            'level' => 'info',
        ]);

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/blockfrost.php' => config_path('blockfrost.php'),
            ], 'config');

            if(!class_exists('CreateIamxUserPaymentsTable')) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_iamx_user_payments_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_iamx_user_payments_table.php'),

                ], 'migrations');
            }

            $this->commands([
                CheckTokenPayment::class,
                ClearOldOpenPayments::class
            ]);
        }

        // Load package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'iamxpaymentgateway');
        $this->loadViewComponentsAs('iamxpaymentgateway', [
            PaymentGatewayConnector::class
        ]);

    }
}
