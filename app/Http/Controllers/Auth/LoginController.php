<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Http\Resources\User as UserResource;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function username()
    {
        return 'username';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $request->user();
            if ($user->token == null) {
                $user->generateToken();
            }
            if ($request->fcm_token) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }
            return new UserResource($user);
        }
        abort(400, __('validation.incorrect_username_or_password'));
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->api_token = null;
            $user->fcm_token = null;
            $user->save();
        }

        return response()->json(null, 204);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }
}
