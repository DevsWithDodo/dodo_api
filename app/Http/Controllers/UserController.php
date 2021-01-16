<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Http\Controllers\CurrencyController;
use App\Http\Resources\User as UserResource;

use App\User;

class UserController extends Controller
{
    public function show()
    {
        $user = auth('api')->user();
        return new UserResource($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_reminder' => ['required', 'string'],
            'fcm_token' => 'required|string',
            'language' => 'required|in:en,hu,it,de',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_reminder' => Crypt::encryptString($request->password_reminder),
            'default_currency' => $request->default_currency,
            'fcm_token' => $request->fcm_token,
            'language' => $request->language
        ]);
        $user->generateToken(); // login
        return response()->json(new UserResource($user), 201);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|password',
            'new_password' => 'required|string|min:4|confirmed',
            'password_reminder' => ['required', 'string'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_reminder' => Crypt::encryptString($request->password_reminder)
        ]);
        return response()->json(null, 204);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'username' => 'string|regex:/^[a-z0-9#.]{3,15}$/|unique:users,username',
            'language' => 'string|in:en,hu,it,de',
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'ad_free' => 'boolean',
            'gradients_enabled' => 'boolean',
            'boosts' => 'integer|min:0',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user->update([
            'username' => $request->username,
            'language' => $request->language,
            'default_currency' => $request->default_currency,
            'ad_free' => $request->ad_free,
            'gradients_enabled' => $request->gradients_enabled,
            'available_boosts' => $user->available_boosts + $request->boosts
        ]);

        return response()->json(null, 204);
    }

    public function passwordReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'exists:users,username'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = User::firstWhere('username', $request->username);
        try {
            $reminder = Crypt::decryptString($user->password_reminder);
            return response()->json(['data' => $reminder]);
        } catch (DecryptException $e) {
            return abort(500, 'Decryption error. Please try again later.');
        }
    }

    public function delete(Request $request)
    {
        $user = $request->user();

        $user->groups()->detach();
        $user->delete();

        return response()->json(null, 204);
    }
}
