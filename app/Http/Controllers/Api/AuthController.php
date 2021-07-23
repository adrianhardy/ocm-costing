<?php

namespace App\Http\Controllers\Api;

use App\Fractal\DataArraySerializer;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Transformers\UserTransformer;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = null;


    /**
     * @param Request $request
     * @return mixed
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);
        return $this->authenticated($request, $this->guard()->user());
    }


    /**
     * @param Request $request
     * @param $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($request->input('onesignal.userId') &&
            $request->input('onesignal.pushToken')
        ) {
            $user->fill([
                'onesignal_user_id' => $request->input('onesignal.userId'),
                'onesignal_token_id' => $request->input('onesignal.pushToken'),
            ]);
            $user->save();
        }
        return fractal($user, new UserTransformer, new DataArraySerializer);
    }
}
