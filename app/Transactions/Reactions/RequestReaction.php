<?php

namespace App\Transactions\Reactions;

use Illuminate\Database\Eloquent\Model;

class RequestReaction extends Model
{
    protected $table = 'request_reactions';
    protected $fillable = ['reaction', 'user_id', 'request_id', 'group_id'];

    public function Request()
    {
        return $this->belongsTo('App\Request');
    }
}
