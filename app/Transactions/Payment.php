<?php

namespace App\Transactions;

use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['amount', 'group_id', 'taker_id', 'payer_id', 'note', 'original_amount', 'original_currency', 'category'];

     protected $casts = [
         'original_amount' => 'encrypted',
         'original_currency' => 'encrypted',
         'amount' => 'encrypted',
         'note' => 'encrypted',
     ];

    
    public function getAmountAttribute($value)
    {
        return ($value == null ? null : decrypt($value));
    }
   
    public function getOriginalAmountAttribute($value)
    {
        return ($value == null ? null : decrypt($value));
    }

    
    public function getOriginalCurrencyAttribute($value)
    {
        return ($value == null ? null : decrypt($value));
    }

    public function getNoteAttribute($value)
    {
        return ($value == null ? null : decrypt($value));
        App::setLocale(auth('api')->user()?->language ?? "en");
        if ($value == '$$legacy_money$$') return __('general.legacy_money');
        if ($value == '$$auto_payment$$') return __('general.auto_payment');
        return $value;
    }

    public function getEdtiableAttribute()
    {
       return $this->group->members()->whereIn('id', [$this->taker_id, $this->payer_id])->count() == 2;
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo('App\User', 'payer_id');
    }

    public function taker(): BelongsTo
    {
        return $this->belongsTo('App\User', 'taker_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo('App\Group', 'group_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }
}
