<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        do{
            $id = strtolower($request->username)."#".sprintf("%04d",rand(1,9999));
        } while(DB::table('users')->select('id')->where('id', $id)->get() == null);
        
        $user = User::create([
            'id' => $id,
            'password' => Hash::make($request->password),
        ]);
        $user->generateToken();
        
        return response()->json(['data' => $user->toArray()], 201);
    }
}
