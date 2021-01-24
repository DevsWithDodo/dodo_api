<?php

namespace App;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;
use App\Notifications\TrialEndedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements HasLocalePreference
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'username', //returns guest if null
        'password',
        'api_token',
        'password_reminder',
        'last_active_group',
        'default_currency',
        'fcm_token',
        'language',
        'ad_free', //true if trial is active
        'gradients_enabled', //true if trial is active
        'available_boosts',
        'trial'
        //is_guest
    ];

    protected $hidden = [
        'password', 'password_reminder'
    ];


    public function getUsernameAttribute($value): string
    {
        return $value ?? __('notifications.guest');
    }

    public function getIsGuestAttribute()
    {
        return $this->password == null;
    }
    /**
     * Decides if the user is registered within the last two weeks.
     */
    public function getTrialAttribute($value): bool
    {
        if (!($value)) return false;
        if ($this->created_at->addWeeks(2) < now()) {
            $this->update(['trial' => 0]);
            $this->notify(new TrialEndedNotification());
            return false;
        }
        return true;
    }

    public function getAdFreeAttribute($value)
    {
        return $this->trial ? 1 : $value;
    }

    public function getGradientsEnabledAttribute($value)
    {
        return $this->trial ? 1 : $value;
    }

    /**
     * Creates an api token for the user that is used for authentication.
     *
     * @return string the token
     */
    public function generateToken()
    {
        $this->api_token = Str::random(60);
        $this->save();

        return $this->api_token;
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->language;
    }

    public function groups()
    {
        return $this
            ->belongsToMany('App\Group', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'balance')
            ->withTimestamps();
    }

    /**
     * Change the guest's id to the user id in the database
     * @param $user_id the id to change
     */
    public function mergeDataInto($user_id)
    {
        DB::table('purchases')->where('buyer_id', $this->id)->update(['buyer_id' => $user_id]);
        DB::table('purchase_receivers')->where('receiver_id', $this->id)->update(['receiver_id' => $user_id]);
        DB::table('payments')->where('payer_id', $this->id)->update(['payer_id' => $user_id]);
        DB::table('payments')->where('taker_id', $this->id)->update(['taker_id' => $user_id]);
        DB::table('requests')->where('requester_id', $this->id)->update(['requester_id' => $user_id]);
    }

    /**
     * Returns the user's total balance calculated from it's groups and their currencies.
     */
    public function totalBalance()
    {
        $this->load('groups');
        $result = 0;
        foreach ($this->groups as $group) {
            $result += CurrencyController::exchangeCurrency(
                from_currency: $group->currency,
                to_currency: $this->default_currency,
                amount: $group->member($this->id)->balance
            );
        }
        return $result;
    }
}
