<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = ['amount', 'group_id', 'taker_id', 'payer_id'];

    public function payer()
    {
        return $this->belongsTo('App\User', 'payer_id');
    }

    public function taker()
    {
        return $this->belongsTo('App\User', 'taker_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }
}
