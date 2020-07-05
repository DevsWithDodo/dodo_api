<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $table = 'receivers';

    protected $fillable = ['amount', 'receiver_id', 'purchase_id'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User', 'receiver_id');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Transactions\Purchase');
    }
}
