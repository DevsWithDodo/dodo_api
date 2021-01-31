<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = ['name', 'group_id', 'buyer_id', 'amount'];

    protected $dispatchesEvents = [
        'creating' => \App\Events\Purchases\PurchaseCreatedEvent::class,
        'updating' => \App\Events\Purchases\PurchaseUpdatedEvent::class,
        'deleting' => \App\Events\Purchases\PurchaseDeletedEvent::class
    ];

    /**
     * Divides the purchase's amount without residue.
     * Deletes/updates existing receivers, and creates new ones according to the arguments.
     * @param array $receivers receiver user ids
     * @return void
     * */
    public function createReceivers(array $receivers)
    {
        $this->load('receivers');

        //delete old receivers
        $old_receivers = [];
        $old_receiver_users = [];
        foreach ($this->receivers as $receiver) {
            if (!(in_array($receiver->receiver_id, $receivers))) {
                $receiver->delete();
            } else {
                $old_receivers[$receiver->receiver_id] = $receiver;
                $old_receiver_users[] = $receiver->receiver_id;
            }
        }
        if (count($receivers) == 0) {
            echo "Deleting purchase (".$this->name. ", ".$this->amount.") in group ".$this->group->id." because it has no receivers.\n";
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
                    'amount' => bcadd($amount_divided, $remainder)
                ]);
            } else {
                //create receiver
                PurchaseReceiver::create([
                    'amount' => bcadd($amount_divided, $remainder),
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

    public function reactions()
    {
        return $this->hasMany('App\Transactions\Reactions\PurchaseReaction', 'purchase_id');
    }
}
