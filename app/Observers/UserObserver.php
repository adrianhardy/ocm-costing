<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * @param User $user
     */
    public function saving(User $user)
    {
        if (!$user->api_token) {
            $user->api_token = Str::random(96);
        }

        if ($user->hasRole('supplier')) {
            $user->password = User::USER_LOCKED;
        }

    }
}
