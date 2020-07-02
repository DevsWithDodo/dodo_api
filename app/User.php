<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];


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
