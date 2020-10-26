<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = ['name', 'group_id', 'buyer_id', 'amount'];

    public function delete()
    {
        $this->receivers()->delete();
        return parent::delete();
    }

    public function group()
    {
        return $this->belongsTo('App\Group');
    }

    public function buyer()
    {
        return $this->belongsTo('App\User', 'buyer_id');
    }

    public function receivers()
    {
        return $this->hasMany('App\Transactions\PurchaseReceiver');
    }
}
