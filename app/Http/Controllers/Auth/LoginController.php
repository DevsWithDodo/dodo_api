<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;

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
        return 'id';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();
            $user->generateToken();

            return response()->json([
                'data' => $user->toArray()
            ]);
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        if($user) {
            $user->api_token = null;
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

    public function isValidId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        return response()->json(User::find($id) == null);
    }

    public function passwordReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        $user = User::find($id);
        if($user == null){
            return response()->json(['error' => 'User does not exist.'], 400);
        }
        return response()->json($user->password_reminder ?? "");

    }
}
