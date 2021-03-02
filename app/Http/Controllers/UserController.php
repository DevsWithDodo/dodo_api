<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
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
    use AuthenticatesUsers;

    public function username()
    {
        return 'username';
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
            'personalised_ads' => 'required|boolean'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_reminder' => Crypt::encryptString($request->password_reminder),
            'default_currency' => $request->default_currency,
            'fcm_token' => $request->fcm_token,
            'language' => $request->language,
            'personalised_ads' => $request->personalised_ads
        ]);
        $user->generateToken(); // login
        return response()->json(new UserResource($user), 201);
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

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
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

    public function show()
    {
        $user = auth('api')->user();
        return new UserResource($user);
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
            'theme' => 'string',
            'ad_free' => 'boolean',
            'gradients_enabled' => 'boolean',
            'boosts' => 'integer|min:0',
            'personalised_ads' => 'in:off,on'
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
            'color_theme' => $request->theme,
            'personalised_ads' => $request->personalised_ads
        ])->filter()->all();
        if (count($data) == 0) abort(400, "The given data to update is empty.");
        if ($data['personalised_ads'] != null) $data['personalised_ads'] = $data['personalised_ads'] == 'on';

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
            return abort(500, 'Decryption error.');
        }
    }

    public function balance(Request $request)
    {
        return response()->json(['data' => [
            'balance' => $request->user()->totalBalance(),
            'currency' => $request->user()->default_currency
        ]]);
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        if ($user->groups->count() > 0) abort(400, __('errors.leave_groups'));
        $user->delete();

        return response()->json(null, 204);
    }
}
