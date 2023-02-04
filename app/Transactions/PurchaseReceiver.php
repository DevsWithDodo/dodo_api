<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiver extends Model
{
    use HasFactory;

    protected $table = 'purchase_receivers';

    protected $fillable = ['amount', 'original_amount', 'receiver_id', 'purchase_id', 'group_id', 'custom_amount'];

    public $timestamps = false;

   public function getAmountAttribute($value)
   {
       return ($value == null ? null : decrypt($value));
   }

   public function getOriginalAmountAttribute($value)
   {
       return ($value == null ? null : decrypt($value));
   }

    public function user()
    {
        return $this->belongsTo('App\User', 'receiver_id');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Transactions\Purchase');
    }

    public function group()
    {
        return $this->belongsTo('App\Group');
    }
}
