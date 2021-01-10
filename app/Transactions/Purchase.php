<?php

namespace App\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Divide the purchase's amount and creates receivers.
     * @param array $receivers receiver ids
     * @return void
     * */
    public function createReceivers(array $receivers)
    {
        $amount_divided = bcdiv($this->amount, count($receivers));
        $remainder = bcsub($this->amount, bcmul($amount_divided, count($receivers)));
        foreach ($receivers as $receiver) {
            PurchaseReceiver::create([
                'amount' => bcadd($amount_divided, $remainder),
                'receiver_id' => $receiver,
                'purchase_id' => $this->id
            ]);
            $remainder = 0;
        }
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
