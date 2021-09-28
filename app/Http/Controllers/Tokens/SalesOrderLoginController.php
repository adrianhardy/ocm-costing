<?php

namespace App\Http\Controllers\Tokens;

use App\Http\Controllers\Controller;
use App\Models\SalesOrderAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SalesOrderLoginController extends Controller
{

    /**
     * @param SalesOrderAuthentication $token
     * @param $verify
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function auth(Request $request)
    {
        $token = SalesOrderAuthentication::query()
            ->where('token', $request->route('token'))
            ->firstOrFail();

        if (!$token->has_expired && !\Auth::check()) {
            Auth::login($token->user);
            Session::put('via_token', $token->token);
        }

        if (\Auth::check()) {
            $this->authorize('view', $token->salesOrder);
            return response()->redirectToRoute('supplier.sales-orders.edit', $token->salesOrder);
        }

        abort(401, 'Unauthorised');
    }
}
