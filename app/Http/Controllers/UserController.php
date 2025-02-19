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
use App\Http\Resources\UserResource as UserResource;
use App\Services\AppleAuthenticationService;
use App\User;
use App\UserStatus;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Log;
use URL;

class UserController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(private AppleAuthenticationService $appleAuthService)
    {
        
    }

    public function validateUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        return response()->json(null, 204);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_reminder' => 'nullable|string', //deprecated
            'fcm_token' => 'nullable|string',
            'language' => 'required|in:en,hu,it,de',
            'personalised_ads' => 'boolean',
            'payment_details' => 'nullable|json'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_reminder' => Crypt::encryptString($request->password_reminder), //deprecated
            'default_currency' => $request->default_currency,
            'fcm_token' => $request->fcm_token ?? null,
            'language' => $request->language,
            'personalised_ads' => $request->personalised_ads ?? false,
            'payment_details' => $request->payment_details ? encrypt($request->payment_details) : null,
            'trial' => true,
        ]);
        $user->generateToken(); // login
        return response()->json(new UserResource($user), 201);
    }

    public function registerWithToken(Request $request) {
        $data = $request->validate([
            'token_type' => 'required|in:google,apple',
            'id_token' => 'required_if:token_type,google|string',
            'auth_code' => 'required_if:token_type,apple|string',
            'device_type' => 'required|in:android,ios,web',
            'fcm_token' => 'nullable|string',
            'language' => 'required|in:en,hu,it,de',
        ]);

        if ($data['token_type'] === 'google') {
            $client = new Google_Client([
                'client_id' => config('oauth.google.client_id')
            ]);
            $payload = $client->verifyIdToken($data['id_token']);
        }
        else if ($data['token_type'] === 'apple') {
            $payload = $this->appleAuthService->verifyAuthCode($data['device_type'], $data['auth_code']);
        }

        if (!$payload) {
            abort(400, __('validation.invalid_token'));
        }
        // Hash the user ID as it directly links to user data, the id is long enough and unique, so salt is not needed
        $hashedUserId = hash('sha256', $payload['sub']); 
        
        if (($user = User::firstWhere($data['token_type'] . '_id', $hashedUserId)) !== null) {
            $user->update([
                'fcm_token' => $data['fcm_token'] ?? null,
                'language' => $data['language'],
            ]);
        } else {
            try {
                // Get currency from user's IP
                $defaultCurrency = Http::get('http://ip-api.com/json/' . $request->ip() . '?fields=status,message,currency')->json()['currency'];
            } catch (\Exception $e) {
                $defaultCurrency = 'EUR';
            }
            $user = User::create([
                'default_currency' => $defaultCurrency,
                'fcm_token' => $data['fcm_token'] ?? null,
                'language' => $data['language'],
                'trial' => true,
                'google_id' => $data['token_type'] === 'google' ? $hashedUserId : null,
                'apple_id' => $data['token_type'] === 'apple' ? $hashedUserId : null,
            ]);
        }
        $user->generateToken();

        return new UserResource($user);
    }

    public function linkSocialLogin(Request $request) {
        $data = $request->validate([
            'token_type' => 'required|in:google,apple',
            'id_token' => 'required_if:token_type,google|string',
            'auth_code' => 'required_if:token_type,apple|string',
            'device_type' => 'required|in:android,ios,web',
        ]);

        if ($data['token_type'] === 'google') {
            $client = new Google_Client([
                'client_id' => config('oauth.google.client_id')
            ]);
            $payload = $client->verifyIdToken($data['id_token']);
        }
        else if ($data['token_type'] === 'apple') {
            $payload = $this->appleAuthService->verifyAuthCode($data['device_type'], $data['auth_code']);
        }

        if (!$payload) {
            abort(400, __('validation.invalid_token'));
        }
        // Hash the user ID as it directly links to user data, the id is long enough and unique, so salt is not needed
        $hashedUserId = hash('sha256', $payload['sub']); 
        
        if (User::firstWhere($data['token_type'] . '_id', $hashedUserId) !== null) {
            abort(400, __('validation.already_linked'));
        }

        $user = $request->user();
        $user->update([
            $data['token_type'] . '_id' => $hashedUserId,
        ]);

        return $user;
    }

    public function createPassword(Request $request) {
        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = $request->user();
        if ($user->has_password) {
            abort(400, __('validation.password_already_set'));
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $user;
    }

    public function signInWithAppleCallback(Request $request) {
        $packageName = config('oauth.apple.android_package_name');
        return redirect(
            "intent://callback?code={$request->get('code')}&id_token={$request->get('id_token')}#Intent;package=$packageName;scheme=signinwithapple;end"
        );
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($request->password, $request->user()->password)) {
            /** @var UserStatus */
            $userStatus = Auth::user()->status;
            $userStatus->update([
                'pin_verified_at' => now(),
                'pin_verification_count' => $userStatus->pin_verification_count + 1,
            ]);
            return $userStatus;
        }
        abort(400, __('validation.incorrect_password'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->api_token === null) {
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
            'old_password' => 'required_with:new_password|string',
            'new_password' => 'string|min:4|confirmed',
            'password_reminder' => 'nullable|string', //deprecated
            'theme' => 'string',
            'ad_free' => 'boolean',
            'gradients_enabled' => 'boolean',
            'boosts' => 'integer|min:0',
            'personalised_ads' => 'in:off,on',
            'payment_details' => 'nullable|json',
            'trial_status' => 'in:seen,trial,expired',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $data = collect([
            'username' => $request->username,
            'language' => $request->language,
            'default_currency' => $request->default_currency,
            'password' => $request->new_password ? Hash::make($request->new_password) : null,
            'password_reminder' => $request->password_reminder ? Crypt::encryptString($request->password_reminder) : null, //deprecated
            'ad_free' => $request->ad_free,
            'gradients_enabled' => $request->gradients_enabled,
            'available_boosts' => $request->boosts ? $user->available_boosts + $request->boosts : null,
            'color_theme' => $request->theme,
            'personalised_ads' => $request->personalised_ads,
            'payment_details' => $request->payment_details ? encrypt($request->payment_details) : null,
        ])->filter()->all();
        if (array_key_exists('personalised_ads', $data)) $data['personalised_ads'] = $data['personalised_ads'] == 'on';

        $user->update($data);
        if ($request->has('trial_status')) {
            $user->status->update(['trial_status' => $request->trial_status]);
        }
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

    public function forgotPassword(Request $request, string $username)
    {
        $user = User::where('username', $request->username)->firstOrFail();

        if($request->hasValidSignature()) {
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:4', 'confirmed'],
            ]);
            if ($validator->fails()) abort(400, $validator->errors()->first());
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(null, 204);
        }

        $validator = Validator::make($request->all(), [
            'group_count' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(CurrencyController::CurrencyList())],
            'groups' => 'required|array|size:'.min(max($request->group_count, 1), 3),
            'groups.*.name' => 'required|string',
            'groups.*.nickname' => 'nullable|string',
            'groups.*.balance' => 'nullable|numeric',
            'groups.*.last_transaction_amount' => 'nullable|numeric',
            'groups.*.last_transaction_date' => 'nullable|date',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $fails = 0;

        $this->testKnowledgeNumber($user->groups->count(), $request->group_count, $fails);
        $this->testKnowledgeString($user->default_currency, $request->currency, $fails);

        foreach ($request->groups as $group_data) {
            $group = $user->groups()->firstWhere('name', $group_data['name']);
            if(!$group){
                $fails = $fails + 2;
                continue;
            }
            $member_data = $group->member($user->id)->member_data;
            $this->testKnowledgeString($member_data->nickname, $group_data['nickname'], $fails);
            $this->testKnowledgeNumber($member_data->balance, $group_data['balance'], $fails, 1);

            $last_payment = $group->payments()->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)->orWhere('taker_id', $user->id);
            })->orderBy('created_at', 'desc')->first();

            $last_purchase_paid = $group->purchases()->where('payer_id', $user->id)->orderBy('created_at', 'desc')->first();
            $last_purchase_received = $group->purchaseReceivers()->where('receiver_id', $user->id)->orderBy('created_at', 'desc')->first();

            $purchase_date_fail = 0;
            $this->testKnowledgeDate($last_payment?->updated_at, $group_data['last_transaction_date'], $purchase_date_fail, 1);
            $this->testKnowledgeDate($last_purchase_paid?->updated_at, $group_data['last_transaction_date'], $purchase_date_fail, 1);
            $this->testKnowledgeDate($last_purchase_received?->updated_at, $group_data['last_transaction_date'], $purchase_date_fail, 1);
            $purchase_amount_fail = 0;
            $this->testKnowledgeNumber($last_payment?->amount, $group_data['last_transaction_amount'], $purchase_amount_fail, 1);
            $this->testKnowledgeNumber($last_purchase_paid?->amount, $group_data['last_transaction_amount'], $purchase_amount_fail, 1);
            $this->testKnowledgeNumber($last_purchase_received?->amount, $group_data['last_transaction_amount'], $purchase_amount_fail, 1);
            if($purchase_date_fail == 3) $fails++;
            if($purchase_amount_fail == 3) $fails++;
        }

        if($fails <= min($request->group_count, 1)) {
            return response()->json([
                'url' => URL::temporarySignedRoute('user.forgot_password', now()->addMinutes(5), ['username' => $user->username])
            ]);
        } else {
            return abort(400, 'The given data is incorrect.');
        }

    }

    private function testKnowledgeString($expected, $given, &$fails)
    {
       if($expected && $expected != $given) $fails++;
    }

    private function testKnowledgeNumber($expected, $given, &$fails, $delta = 0)
    {
        if($expected &&abs($expected - $given) > $delta) $fails++;
    }

    private function testKnowledgeDate($expected, $given, &$fails, $delta = 0)
    {
        if($expected && Carbon::parse($expected)->diff(Carbon::parse($given))->days > $delta) $fails++;
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
