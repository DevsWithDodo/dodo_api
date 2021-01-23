<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents the items on the shopping lists.
 */
class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = ['name', 'group_id', 'requester_id'];

    public function requester()
    {
        return $this->belongsTo('App\User', 'requester_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }

    public function reactions()
    {
        return $this->hasMany('App\Transactions\Reactions\RequestReaction', 'request_id');
    }
}
