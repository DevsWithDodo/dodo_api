<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\CurrencyController;
use App\Http\Resources\User as UserResource;

use App\User;

class UserController extends Controller
{
    public function show()
    {
        $user = Auth::guard('api')->user();
        return new UserResource($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_reminder' => ['nullable', 'string'],
            'fcm_token' => 'required|string'
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['function' => 'UserController@register']);
            abort(400, "0");
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_reminder' => $request->password_reminder ?? null,
            'default_currency' => $request->default_currency,
            'fcm_token' => $request->fcm_token
        ]);
        $user->generateToken(); // login
        return response()->json(new UserResource($user), 201);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            //'new_password' => 'required|string|min:4|confirmed',
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['id' => Auth::guard('api')->user()->id, 'function' => 'UserController@changePassword']);
            abort(400, "0");
        }

        try {
            if ((Hash::check($request->old_password, $user->password)) == false) abort(400, "11");
            else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) abort(400, "12");
            else {
                $user->update(['password' => Hash::make($request->new_password)]);
                return response()->json(null, 204);
            }
        } catch (\Exception $ex) {
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            } else {
                $msg = $ex->getMessage();
            }
            return response()->json(["error" => $msg], 500);
        }
    }

    public function changeUsername(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'new_username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['id' => Auth::guard('api')->user()->id, 'function' => 'UserController@changeUsername']);
            abort(400, "0");
        }

        $user->update(['username' => $request->new_username]);

        return response()->json(null, 204);
    }

    public function passwordReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'exists:users,username'],
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['function' => 'UserController@passwordReminder']);
            abort(400, "0");
        }
        $user = User::firstWhere('username', $request->username);

        return response()->json(['data' => $user->password_reminder]);
    }
}
