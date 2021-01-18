<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = ['name', 'currency', 'anyone_can_invite', 'invitation', 'boosted'];

    public function getMemberLimitAttribute()
    {
        return $this->boosted ? 30 : 8;
    }

    public static function nicknameOf($group_id, $user_id)
    {
        return Cache::remember('group_' . $group_id . "_nicknames", now()->addSeconds(5), function () use ($group_id) {
            $nicknames = [];
            $group = Group::with('members')->findOrFail($group_id);
            foreach ($group->members as $member) {
                $nicknames[$member->id] = $member->member_data->nickname;
            }
            return $nicknames;
        })[$user_id] ?? '$$deleted_member$$';
    }

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
            ->withPivot('nickname', 'is_admin', 'balance')
            ->withTimestamps();
    }

    public function member($user_id)
    {
        return $this->members()->findOrFail($user_id);
    }

    public function addToMemberBalance($user_id, $amount)
    {
        $member = $this->member($user_id);
        $old_balance = $member->member_data->balance;
        $member->member_data->update(['balance' => bcadd($old_balance, $amount)]);
        if (config('app.debug'))
            Log::info('updated member balance', ['user id' => $user_id, 'amount' => $amount, 'old balance' => $old_balance, 'new balance' => $member->member_data->balance]);
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

    public function recalculateBalances()
    {
        foreach ($this->members as $member) {
            $balance = 0;
            $payments_payed = $this->payments()->where('payer_id', $member->id)->get();
            $payments_taken = $this->payments()->where('taker_id', $member->id)->get();
            $purchases_buyed = $this->purchases()->where('buyer_id', $member->id)->get();
            $purchases_received = DB::table('purchase_receivers')
                ->join('purchases', 'purchase_receivers.purchase_id', '=', 'purchases.id')
                ->where([
                    ['purchase_receivers.receiver_id', $member->id],
                    ['purchases.group_id', $this->id]
                ])->select('purchase_receivers.amount')->get();
            foreach ($payments_payed as $payment_payed) {
                $balance = bcadd($balance, $payment_payed->amount);
            }
            foreach ($payments_taken as $payment_taken) {
                $balance = bcsub($balance, $payment_taken->amount);
            }
            foreach ($purchases_buyed as $purchase_buyed) {
                $balance = bcadd($balance, $purchase_buyed->amount);
            }
            foreach ($purchases_received as $purchase_received) {
                $balance = bcsub($balance, $purchase_received->amount);
            }
            $member->member_data->update(['balance' => $balance]);
        }
    }
}
