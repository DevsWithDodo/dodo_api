<?php

namespace App\Transactions;

use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = ['name', 'group_id', 'buyer_id', 'amount', 'original_amount', 'original_currency'];

    public static function divideAmount() {

    }

    /**
     * Divides the purchase's amount without residue.
     * Deletes/updates existing receivers, and creates new ones according to the arguments.
     * @param array $receivers receiver user ids
     * @return void
     * */
    public function createReceivers(array $receivers)
    {
        $this->load('receivers');
        $receivers = array_unique($receivers);

        //delete old receivers
        $old_receivers = [];
        $old_receiver_users = [];
        foreach ($this->receivers as $receiver) {
            if (!(in_array($receiver->receiver_id, $receivers))) {
                $receiver->delete();
            } else {
                if (isset($old_receivers[$receiver->receiver_id])) {
                    self::withoutEvents(function () use ($receiver) {
                        $receiver->delete();
                    });
                }
                $old_receivers[$receiver->receiver_id] = $receiver;
                $old_receiver_users[] = $receiver->receiver_id;
            }
        }
        if (count($receivers) == 0) {
            echo "Deleting purchase (" . $this->name . ", " . $this->amount . ") in group " . $this->group->id . " because it has no receivers.\n";
            Log::info("Deleting purchase because it has no receivers.", ['purchase' => $this]);
            $this->delete();
            return;
        }
        $amount_divided = bcdiv($this->amount, count($receivers));
        $remainder = bcsub($this->amount, bcmul($amount_divided, count($receivers)));
        foreach ($receivers as $receiver_user) {
            if (in_array($receiver_user, $old_receiver_users)) {
                //update receiver
                $old_receivers[$receiver_user]->update([
                    'amount' => bcadd($amount_divided, $remainder),
                    'original_amount' => bcadd($amount_divided, $remainder)
                ]);
            } else {
                //create receiver
                PurchaseReceiver::create([
                    'amount' => bcadd($amount_divided, $remainder),
                    'original_amount' => bcadd($amount_divided, $remainder),
                    'receiver_id' => $receiver_user,
                    'purchase_id' => $this->id,
                    'group_id' => $this->group_id
                ]);
            }
            $remainder = 0;
        }
    }

    /**
     * Deletes, recalculates, and recreates the receivers of the purchase.
     * Should be used only for testing purposes as this can take a while.
     * @return void
     * */
    public function recalculateReceivers()
    {
        $receivers = $this->receivers->map(function ($item, $key) {
            return $item->user->id;
        });
        $this->receivers()->delete();
        $this->withoutEvents(function () use ($receivers) {
            $this->createReceivers($receivers->toArray());
        });
    }

    public function delete()
    {
        foreach ($this->receivers as $receiver) {
            $receiver->delete();
        }
        return parent::delete();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo('App\Group');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo('App\User', 'buyer_id');
    }

    public function receivers(): HasMany
    {
        return $this->hasMany('App\Transactions\PurchaseReceiver');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }
}
