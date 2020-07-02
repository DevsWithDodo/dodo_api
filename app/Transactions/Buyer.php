<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $table = 'buyers';

    protected $fillable = ['amount'];

    public function user()
    {
        return $this->belongsTo('App\User', 'buyer_id');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Transactions\Purchase');
    }
}
