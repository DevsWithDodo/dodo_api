<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = ['name', 'currency', 'anyone_can_invite', 'invitation'];

    public function delete()
    {
        $this->members()->detach($this->members);
        $this->purchases()->delete();
        $this->payments()->delete();
        $this->requests()->delete();

        return parent::delete();
    }

    /**
     * The groups that the user in.
     */
    public function members()
    {
        return $this
            ->belongsToMany('App\User', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'group_id')
            ->withTimestamps();
    }

    public function balances()
    {
        return Cache::remember($this->id . '_balances', 300, function () {
            $data = [];
            foreach ($this->members as $member) {
                $payment_payed = $this->payments()->where('payer_id', $member->id)->sum('amount');
                $payment_taken = $this->payments()->where('taker_id', $member->id)->sum('amount');
                $purchase_buyed = $this->purchases()->where('buyer_id', $member->id)->sum('amount');
                $purchase_received = DB::table('purchase_receivers')
                    ->join('purchases', 'purchase_receivers.purchase_id', '=', 'purchases.id')
                    ->where([
                        ['purchase_receivers.receiver_id', $member->id],
                        ['purchases.group_id', $this->id]
                    ])->sum('purchase_receivers.amount');
                $data[$member->id] = $payment_payed - $payment_taken + $purchase_buyed - $purchase_received;
            }
            return $data;
        });
    }

    public function guests()
    {
        return $this->members()->where('password', null);
    }

    public function admins()
    {
        return $this->members()->where('is_admin', true);
    }

    public function purchases()
    {
        return $this->hasMany('App\Transactions\Purchase');
    }

    public function payments()
    {
        return $this->hasMany('App\Transactions\Payment');
    }

    public function requests()
    {
        return $this->hasMany('App\Request');
    }
}
