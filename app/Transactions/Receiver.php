<?php

namespace App\Depts;

use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $table = 'buyers';

    protected $fillable = ['amount'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Transactions\Purchase');
    }
}
