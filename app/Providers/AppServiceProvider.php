<?php

namespace App\Providers;

use App\Models;
use App\Models\User;
use App\Observers\CurrencyObserver;
use App\Observers\DailyPricingObserver;
use App\Observers\InvoiceLineObserver;
use App\Observers\InvoiceObserver;
use App\Observers\ProductObserver;
use App\Observers\SalesOrderLineObserver;
use App\Observers\SalesOrderObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use \Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // spin up the code gen library when we're developing locally
        if ($this->app->environment('local')) {
            $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Models\Product::observe(ProductObserver::class);
        Models\SalesOrder::observe(SalesOrderObserver::class);
        Models\SalesOrderLine::observe(SalesOrderLineObserver::class);
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return \Auth::user() && Hash::check($value, \Auth::user()->password);
        });
        Models\Currency::observe(CurrencyObserver::class);
        Models\Invoice::observe(InvoiceObserver::class);
        Models\InvoiceLine::observe(InvoiceLineObserver::class);
        Models\DailyPricing::observe(DailyPricingObserver::class);

    }
}
