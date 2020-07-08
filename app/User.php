<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'password', 'registered', 'email', 'api_token'
    ];

    protected $hidden = [
        'password',
    ];

    public function generateToken()
    {
        $this->api_token = Str::random(60);
        $this->save();

        return $this->api_token;
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
}
