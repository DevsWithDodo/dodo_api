<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $table = 'invitations';

    protected $fillable = [
        'group_id', 'token', 'usable_once_only'
    ];

    public function group()
    {
        return $this->belongsTo('App\Group');
    }
}
