<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = ['name'];

    /**
     * The groups that the user in.
     */
    public function members()
    {
        return $this
            ->belongsToMany('App\User', 'group_user')
            ->as('member_data')
            ->withPivot('balance', 'nickname', 'is_admin')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany('App\Transactions\Purchase');
    }

    public function payments()
    {
        return $this->hasMany('App\Transactions\Payment');
    }

    public function requests()
    {
        return $this->hasMany('App\Request');
    }

}
