<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    /**
     * The groups that the user in.
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'group_user');
    }

}
