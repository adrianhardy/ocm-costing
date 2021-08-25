<?php

namespace App\Providers;

use App\Models;
use App\Models\User;
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
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return \Auth::user() && Hash::check($value, \Auth::user()->password);
        });
    }
}
