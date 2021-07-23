<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cookie;

class UnsetAuthenticatedUserCookie
{

    /**
     * @param Logout $event
     */
    public function handle(Logout $event)
    {

    }
}
