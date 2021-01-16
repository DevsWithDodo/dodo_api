<?php

namespace App\Transactions\Reactions;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReaction extends Model
{
    //use HasFactory;
    protected $table = 'payment_reactions';
    protected $fillable = ['reaction', 'user_id', 'payment_id', 'group_id'];

    public function Payment()
    {
        return $this->belongsTo('App\Transactions\Payment');
    }
}
