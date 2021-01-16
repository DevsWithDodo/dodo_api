<?php

namespace App\Transactions\Reactions;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReaction extends Model
{
    //use HasFactory;
    protected $table = 'purchase_reactions';
    protected $fillable = ['reaction', 'user_id', 'purchase_id', 'group_id'];

    public function Purchase()
    {
        return $this->belongsTo('App\Transactions\Purchase');
    }
}
