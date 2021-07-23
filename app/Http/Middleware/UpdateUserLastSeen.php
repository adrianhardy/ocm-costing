<?php

namespace App\Http\Middleware;

use Closure;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!\Auth::check()) {
            return $next($request);
        }

        $user = \Auth::user();

        $user->update([
            'last_seen_at' => new \DateTime(),
        ]);

        return $next($request);
    }
}
