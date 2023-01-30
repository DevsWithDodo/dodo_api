<?php

namespace App;

use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Represents the items on the shopping lists.
 */
class Request extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requests';

    protected $fillable = ['name', 'group_id', 'requester_id'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo('App\User', 'requester_id');
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
