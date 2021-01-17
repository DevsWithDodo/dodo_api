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

    public function update(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'username' => 'string|regex:/^[a-z0-9#.]{3,15}$/|unique:users,username',
            'language' => 'string|in:en,hu,it,de',
            'default_currency' => ['string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'old_password' => 'required_with:new_password|string|password',
            'new_password' => 'string|min:4|confirmed',
            'password_reminder' => 'required_with:new_password|string',
            'ad_free' => 'boolean',
            'gradients_enabled' => 'boolean',
            'boosts' => 'integer|min:0',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $data = collect([
            'username' => $request->username,
            'language' => $request->language,
            'default_currency' => $request->default_currency,
            'password' => $request->new_password ? Hash::make($request->new_password) : null,
            'password_reminder' => $request->new_password ? Crypt::encryptString($request->password_reminder) : null,
            'ad_free' => $request->ad_free,
            'gradients_enabled' => $request->gradients_enabled,
            'available_boosts' => $request->boosts ? $user->available_boosts + $request->boosts : null,
        ])->filter()->all();
        if (count($data) == 0) abort(400, "The given data to update is empty.");

        $user->update($data);
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
        if ($user->groups->count() > 0) abort(400, __('validation.leave_groups'));
        $user->delete();

        return response()->json(null, 204);
    }
}
