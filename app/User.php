<?php

namespace App;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements HasLocalePreference
{
    use Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'api_token',
        'password_reminder',
        'last_active_group',
        'default_currency',
        'fcm_token',
        'language',
        'ad_free',
        'gradients_enabled',
        'available_boosts'
    ];

    protected $hidden = [
        'password', 'password_reminder'
    ];


    public function getUsernameAttribute($value)
    {
        return $value ?? __('notifications.guest');
    }

    public function getAdFreeAttribute($value)
    {
        if ($this->created_at->addWeeks(2) < now()) return $value;
        else return 1;
    }

    public function getGradientsEnabledAttribute($value)
    {
        if ($this->created_at->addWeeks(2) < now()) return $value;
        else return 1;
    }

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
        return 'hu'; //$this->language;
    }

    //The groups that the user in:
    public function groups()
    {
        return $this
            ->belongsToMany('App\Group', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'balance')
            ->withTimestamps();
    }

    public function memberIn($group_id)
    {
        return $this->groups()->where('group_id', $group_id)->first();
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
        return 0;
        //TODO optimize
        $currencies = CurrencyController::currencyRates();
        $base = $currencies['base'];
        $rates = $currencies['rates'];
        $result_currency = $this->default_currency;

        $result = 0;
        foreach ($this->groups as $group) {
            $group_balance = $group->member($this->id)->balance;
            $group_currency = $group->currency;
            if ($group_currency == $result_currency) {
                $result += $group_balance;
            } else {
                //convert to base currency
                $in_base = $group_balance   / (($group_currency == $base)   ? 1 : ($rates[$group_currency]  ?? abort(500, "Invalid currency.")));
                //convert to result currency
                $result += $in_base         * (($result_currency == $base)  ? 1 : ($rates[$result_currency] ?? abort(500, "Invalid currency.")));
            }
        }
        return $result;
    }

    public function isGuest()
    {
        return $this->username == null;
    }
}
