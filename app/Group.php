<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = ['name', 'currency', 'anyone_can_invite', 'invitation'];

    public function delete(){
        $this->members()->detach($this->members);
        $this->transactions()->delete();
        $this->payments()->delete();
        $this->requests()->delete();

        return parent::delete();
    }
    
    /**
     * The groups that the user in.
     */
    public function members()
    {
        return $this
            ->belongsToMany('App\User', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'group_id')
            ->withTimestamps();
    }

    public function guests()
    {
        return $this->members()->where('password', null);
    }

    public function admins()
    {
        return $this->members()->where('is_admin', true);
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
