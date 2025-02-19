<?php

namespace App;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;
use Illuminate\Database\Eloquent\Builder;
use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;

class User extends Authenticatable implements HasLocalePreference {
    use Notifiable, HasFactory;

    protected static function booted()
    {
        static::created(function ($user) {
            $user->status()->create([
                'pin_verified_at' => now(),
                'pin_verification_count' => 0,
                'trial_status' => $user->trial ? 'trial' : 'seen',
            ]);
        });

        static::updating(function ($user) {
            if ($user->isDirty('trial') && $user->trial == false) {
                $user->status->update(['trial_status' => 'expired']);
            }
        });
    }

    protected $fillable = [
        'username', //null on guests
        'password', //null on guests
        'api_token',
        'password_reminder', //deprecated
        'last_active_group',
        'default_currency',
        'fcm_token',
        'language',
        'color_theme',
        'ad_free', //true if trial is active
        'gradients_enabled', //true if trial is active
        'available_boosts',
        'trial',
        'personalised_ads',
        'payment_details',
        'google_id',
        'apple_id'
        //is_guest
    ];

    protected $hidden = [
        'password', 
        'password_reminder',
        'google_id',
        'apple_id'
    ];

    protected $casts = [
        'trial' => 'boolean',
        'ad_free' => 'boolean',
        'gradients_enabled' => 'boolean',
        'personalised_ads' => 'boolean',
        'payment_details' => 'array'
    ];

    public function getGoogleConnectedAttribute(): bool {
        return $this->google_id !== null;
    }

    public function getAppleConnectedAttribute(): bool {
        return $this->apple_id !== null;
    }

    public function getHasPasswordAttribute(): bool {
        return $this->password !== null;
    }

    public function getIsGuestAttribute() {
        return $this->password == null && $this->google_id === null && $this->apple_id === null;
    }

    public function getPaymentDetailsAttribute($value) {
        return $value ? decrypt($value) : null;
    }

    public function getAdFreeAttribute($value) {
        return $this->trial ? 1 : $value;
    }

    public function getGradientsEnabledAttribute($value) {
        return $this->trial ? 1 : $value;
    }

    public function sendNotification($notification) {
        if($this->id != auth('api')->user()?->id){
            try {
                $this->notify($notification->locale($this->language));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }

    /**
     * Creates an api token for the user that is used for authentication.
     *
     * @return string the token
     */
    public function generateToken(): string {
	    $token = Str::random(60);
        $this->api_token = $token;
        $this->save();
        return $this->api_token;
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string
     */
    public function routeNotificationForFcm() {
        return $this->fcm_token;
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale() {
        return $this->language;
    }

    public function groups(): BelongsToMany {
        return $this
            ->belongsToMany(Group::class)
            ->using(Member::class)
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'balance', 'approved')
            ->where('approved', true)
            ->withTimestamps();
    }
    public function purchases(): HasMany {
        return $this->hasMany(Purchase::class, 'buyer_id');
    }

    public function purchaseReceivers(): HasMany {
        return $this->hasMany(PurchaseReceiver::class, 'receiver_id');
    }

    public function receivedPurchases() {
        return $this->purchaseReceivers()->with('purchase');
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    public function requests(): HasMany {
        return $this->hasMany(Request::class, 'requester_id');
    }

    public function reactions(): HasMany {
        return $this->hasMany(Reaction::class, 'user_id');
    }

    public function status(): HasOne {
        return $this->hasOne(UserStatus::class);
    }

    /**
     * Change the guest's id to the user id in the database
     * @param $user_id the id to change
     */
    public function mergeDataInto($user_id) {
        DB::table('purchases')->where('buyer_id', $this->id)->update(['buyer_id' => $user_id]);
        DB::table('purchase_receivers')->where('receiver_id', $this->id)->update(['receiver_id' => $user_id]);
        DB::table('payments')->where('payer_id', $this->id)->update(['payer_id' => $user_id]);
        DB::table('payments')->where('taker_id', $this->id)->update(['taker_id' => $user_id]);
        DB::table('requests')->where('requester_id', $this->id)->update(['requester_id' => $user_id]);
    }

    /**
     * Returns the user's total balance calculated from it's groups and their currencies.
     */
    public function totalBalance() {
        $result = 0;
        foreach ($this->groups as $group) {
            $result += CurrencyController::exchangeCurrency(
                from_currency: $group->currency,
                to_currency: $this->default_currency,
                amount: $group->member($this->id)->member_data->balance
            );
        }
        return $result;
    }

    /**
     * @param $lastActive the time in days since the user was last active.
     * Setting it to -1 will return the users that have ever been active.
     * @return Collection the users that were active in the last $lastActive days
     */
    public static function activeUserQuery($lastActive = 30): Builder {
        return User::where(function ($query) use ($lastActive) {
            if ($lastActive == -1) {
                $query
                    ->has('purchases')
                    ->orHas('payments')
                    ->orHas('requests')
                    ->orHas('reactions');
            } else {
            }
            $query
                ->whereHas('purchases', function ($query) use ($lastActive) {
                    $query->where('updated_at', '>', now()->subDays($lastActive));
                })
                ->orWhereHas('payments', function ($query) use ($lastActive) {
                    $query->where('updated_at', '>', now()->subDays($lastActive));
                })
                ->orWhereHas('requests', function ($query) use ($lastActive) {
                    $query->where('updated_at', '>', now()->subDays($lastActive));
                })
                ->orWhereHas('reactions', function ($query) use ($lastActive) {
                    $query->where('updated_at', '>', now()->subDays($lastActive));
                });
        });
    }
}
