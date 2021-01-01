<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiver extends Model
{
    use HasFactory;

    protected $table = 'purchase_receivers';

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
