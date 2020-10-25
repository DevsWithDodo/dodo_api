<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password', 'api_token', 'password_reminder', 'last_active_group', 'default_currency', 'fcm_token'
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

    //The groups that the user in:
    public function groups()
    {
        return $this
            ->belongsToMany('App\Group', 'group_user')
            ->as('member_data')
            ->withPivot('balance', 'nickname', 'is_admin')
            ->withTimestamps();
    }

    /* Transaction relations */

    public function buyed()
    {
        return $this->hasMany('App\Transactions\Buyer', 'buyer_id');
    }

    public function received()
    {
        return $this->hasMany('App\Transactions\Receiver', 'receiver_id');
    }

    /* Payment relations */

    public function payed()
    {
        return $this->hasMany('App\Transactions\Payment', 'payer_id');
    }

    public function taken()
    {
        return $this->hasMany('App\Transactions\Payment', 'taker_id');
    }


    /**
     * Returns the user's total balance calculated from it's groups and their currencies.
     */
    public function balance()
    {
        $currencies = CurrencyController::currencyRates();
        $base = $currencies['base'];
        $rates = $currencies['rates'];
        $result_currency = $this->default_currency;
        
        $result = 0;
        foreach ($this->groups as $group) {
            $group_balance = $group->member_data->balance;
            $group_currency = $group->currency;
            if($group_currency == $result_currency){
                $result += $group_balance;
            } else {
                //convert to base currency
                $in_base = $group_balance   / (($group_currency == $base)   ? 1 : ($rates[$group_currency]  ?? abort(500, "Invalid currency.")));
                //convert to result currency
                $result += $in_base         * (($result_currency == $base)  ? 1 : ($rates[$result_currency] ?? abort(500, "Invalid currency.")));
            }
        }
        return ['amount' => $result, 'currency' => $result_currency];
    }

    public function isGuest()
    {
        return $this->password == null;
    }
}
