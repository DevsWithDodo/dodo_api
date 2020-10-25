<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            ->withPivot('nickname', 'is_admin')
            ->withTimestamps();
    }

    public function balance(Group $group)
    {
        $payment_payed = $group->payments->where('payer_id', $this->id)->sum('amount');
        $payment_taken = $group->payments->where('taker_id', $this->id)->sum('amount');
        $purchase_buyed = $group->transactions->where('buyer_id', $this->id)->sum('amount');
        $purchase_received = DB::table('purchase_receivers')
            ->join('purchases', 'purchase_receivers.purchase_id', '=', 'purchases.id')
            ->where([
                ['purchase_receivers.receiver_id', $this->id],
                ['purchases.group_id', $group->id]
            ])->sum('purchase_receivers.amount');
        return $payment_payed - $payment_taken + $purchase_buyed - $purchase_received;
    }

    /**
     * Returns the user's total balance calculated from it's groups and their currencies.
     */
    public function totalBalance()
    {
        $currencies = CurrencyController::currencyRates();
        $base = $currencies['base'];
        $rates = $currencies['rates'];
        $result_currency = $this->default_currency;
        
        $result = 0;
        foreach ($this->groups as $group) {
            $group_balance = $this->balance($group);
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
        return $result;
    }

    public function isGuest()
    {
        return $this->password == null;
    }
}
