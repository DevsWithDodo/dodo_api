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
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_reminder' => ['string']
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        
        if(User::find($id) == null){
            $user = User::create([
                'id' => $id,
                'password' => Hash::make($request->password),
                'password_reminder' => $request->password_reminder ?? null
            ]);
            $user->generateToken(); // login 

            return response()->json(['data' => $user->toArray()], 201);
        } else {
            return response()->json(['error' => "Id is already taken."], 400);
        }
        
        
        
        
    }
}
