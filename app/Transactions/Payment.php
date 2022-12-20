<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Log;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['amount', 'group_id', 'taker_id', 'payer_id', 'note'];

    public function getNoteAttribute($value)
    {
        App::setLocale(auth('api')->user()?->language ?? "en");
        if ($value == '$$legacy_money$$') return __('general.legacy_money');
        if ($value == '$$auto_payment$$') return __('general.auto_payment');
        return $value;
    }

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

    public function reactions()
    {
        return $this->hasMany('App\Transactions\Reactions\PaymentReaction', 'payment_id');
    }
}
