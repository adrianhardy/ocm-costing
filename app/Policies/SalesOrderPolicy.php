<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\SalesOrderAuthentication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, SalesOrder $salesOrder)
    {

        if ($user->hasRole(['super', 'admin'])) {
            return true;
        }

        return true;

        $token = $salesOrder->tokens()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })->exists();

        if ($token) {
            return true;
        }

        // Does this person have a "general" token that's active and does
        // the user belong to the same supplier as the sales order?
        $token = SalesOrderAuthentication::query()
            ->whereHas('user', function($query) use ($user, $salesOrder) {
                $query
                    ->where('id', $user->id)
                    ->where('supplier_id', $salesOrder->supplier_id);
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })->exists();

        return $token;
    }
}
