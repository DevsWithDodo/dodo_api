<?php

namespace App\Transactions;

use App\Http\Controllers\CurrencyController;
use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = ['name', 'group_id', 'buyer_id', 'amount', 'original_amount', 'original_currency', 'category'];

    public static function createWithReceivers(array $purchase_data) {
        $purchase = new Purchase();
        $purchase->group_id = $purchase_data['group_id'];
        $purchase->updateWithReceivers($purchase_data);
    }

    public function updateWithReceivers(array $purchase_data) {
        $this->name = $purchase_data['name'];
        $this->buyer_id = $purchase_data['buyer_id'];
        $this->original_currency = $purchase_data['original_currency'];
        $this->amount = CurrencyController::exchangeCurrency($purchase_data['original_currency'], $purchase_data['group_currency'], $purchase_data['amount']);
        $this->original_amount = $purchase_data['amount'];
        $this->category = $purchase_data['category'];
        $this->save();

        $this->syncReceivers($purchase_data['receivers'], $purchase_data['original_currency'], $purchase_data['group_currency']);
        $this->touch();
    }

    /**
     * Converts and divides the purchase's amount without residue.
     * Deletes/updates existing receivers, and creates new ones according to the arguments.
     * @param array $receivers array of receivers with "user_id" and (original) "amount"|null
     * @param string $original_currency
     * @param string $group_currency
     *
     * @return void
     * */
    public function syncReceivers(array $receivers, $original_currency, $group_currency)
    {
        $this->load('receivers');
        //convert custom amount to group currency
        $receivers = array_map(fn($r) => [
            'user_id' => $r['user_id'],
            'amount' => isset($r['amount']) ? CurrencyController::exchangeCurrency($original_currency, $group_currency, $r['amount']) : null,
            'original_amount' => $r['amount'] ?? null
        ], $receivers);
        $receiver_user_ids = array_column($receivers, 'user_id');
        $old_receivers_to_update = [];
        //handle old receivers
        foreach ($this->receivers as $receiver) {
            if(in_array($receiver->user_id, $receiver_user_ids)) {
                $old_receivers_to_update[$receiver->receiver_id] = $receiver;
            } else {
                $receiver->delete();
            }
        }
        //calculate amount to be divided
        $custom_receivers = array_filter($receivers, fn($r) => isset($r['amount']));
        $custom_amount_sum = array_reduce($custom_receivers, fn($carry, $r) => bcadd($carry, $r['amount']), 0);
        $amount_to_be_divided = bcsub($this->amount, $custom_amount_sum);
        $divide_count = count($receivers) - count($custom_receivers);
        if(!(count($custom_receivers) < count($receivers))) {
            abort(400, "All receivers have custom amounts.");
        }
        $amount_divided = bcdiv($amount_to_be_divided, $divide_count);
        $remainder = bcsub($amount_to_be_divided, bcmul($amount_divided, $divide_count));
        foreach ($receivers as $receiver_data) {
            //create or update receiver
            if (isset($old_receivers_to_update[$receiver_data['user_id']])) {
                $receiver = $old_receivers_to_update[$receiver_data['user_id']];
            } else {
                $receiver = new PurchaseReceiver([
                    'purchase_id' => $this->id,
                    'receiver_id' => $receiver_data['user_id'],
                    'group_id' => $this->group_id,
                    'receiver_id' => $receiver_data['user_id'],
                ]);
            }
            if(isset($receiver_data['amount'])) {
                //set custom amount
                $receiver->amount = $receiver_data['amount'];
                $receiver->original_amount = $receiver_data['original_amount'];
                $receiver->custom_amount = true;
            } else {
                //set divided amount, add remainder to first receiver_data
                $amount = bcadd($amount_divided, $remainder);
                $receiver->amount = $amount;
                $receiver->original_amount = CurrencyController::exchangeCurrency($group_currency, $original_currency, $amount);
                $receiver->custom_amount = false;
                $remainder = 0;
            }
            $receiver->save();
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
