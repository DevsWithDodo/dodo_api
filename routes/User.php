<?php

namespace App;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements HasLocalePreference
{
    use Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password', 'api_token', 'password_reminder', 'last_active_group', 'default_currency', 'fcm_token', 'language'
    ];

    protected $hidden = [
        'password', 'password_reminder'
    ];

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

    //The groups that the user in:
    public function groups()
    {
        return $this
            ->belongsToMany('App\Group', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin')
            ->withTimestamps();
    }

    /**
     * Returns the user's total balance calculated from it's groups and their currencies.
     */
    public function totalBalance()
    {
        //TODO Cache
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
        return $this->password == null;
    }
}
