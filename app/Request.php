<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'requests';

    protected $fillable = ['name', 'group_id', 'requester_id', 'fulfiller_id', 'fulfilled_at'];

    public function requester()
    {
        return $this->belongsTo('App\User', 'requester_id');
    }

    public function fulfiller()
    {
        return $this->belongsTo('App\User', 'fulfiller_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }
}
