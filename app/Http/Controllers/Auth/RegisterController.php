<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registering
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
        $this->middleware('guest');
    }

    public function registered(Request $request, $user)
    {
        $user->generateToken();
        
        return response()->json(['data' => $user->toArray()], 201);
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'registered' => isset($data['email']),
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Registering email to a 'guest' user.
     */
    public function registerEmail(Request $request)
    {
        $user = Auth::guard('api')->user();

        if(!$user){
            return response()->json(['error' => 'User not found'], 404);
        }
        if($user->registered){
            return response()->json(['error' => 'User already registered with email'], 400);
        }

        $request->validate([
            'email' => 'required|string|unique:users',
            'password' => 'required|string|password:api',
        ]);
        
        $user->email = $request->email;
        $user->registered = true;
        $user->save();
        
        return response()->json(['data' => $user->toArray()], 200);
    }
}
