<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Cookie;

class SetAuthenticatedUserCookie
{
    /**
     * @param Authenticated $event
     */
    public function handle(Authenticated $event)
    {

    }
}
