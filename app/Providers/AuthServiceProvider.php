<?php

namespace App\Providers;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models;
use App\Policies;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Models\SalesOrder::class => Policies\SalesOrderPolicy::class,
        Models\Product::class => Policies\ProductPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Override @can('__china') and @can('__uk') so that as a priority, they
         * pay attention to the domain THEN they'll check permissions.
         *
         * Gate::before doesn't work out of the box, so we've had to do this
         * Gate::after, so that we can catch @can('china'). However, spatie permission
         * is getting there first and allowing the call, because we have those
         * permissions granted to supers.
         *
         * So @can('__china') now checks here to say can they 'china' and what's the
         * domain
         */
        Gate::after(function (Models\User $user, $ability) {
            $ukDomain = in_array(request()->getHost(), config('domains.uk'));
            $cnDomain = in_array(request()->getHost(), config('domains.cn'));
            if ($ability === '__uk' && $user->can('uk') && $ukDomain) {
                return true;
            }
            if ($ability === '__china' && $user->can('china') && $cnDomain) {
                return true;
            }
            if (($ukDomain || $cnDomain) && in_array($ability, ['__china', '__uk'])) {
                return false;
            }
        });
    }
}
