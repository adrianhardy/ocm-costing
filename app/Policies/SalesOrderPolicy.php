<?php

namespace App\Policies;

use App\Models\SalesOrder;
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

        $token = $salesOrder->tokens()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })->exists();

        return $token;
    }
}
