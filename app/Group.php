<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = ['name', 'currency', 'anyone_can_invite'];

    public function delete(){
        $this->members()->detach($this->members);
        foreach ($this->transactions as $purchase) {
            $purchase->buyer->delete();
            $purchase->receivers()->delete();
        }
        $this->transactions()->delete();
        $this->payments()->delete();
        $this->requests()->delete();
        $this->invitations()->delete();

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
            ->withPivot('balance', 'nickname', 'is_admin')
            ->withTimestamps();
    }

    public function guests()
    {
        return $this->members()->where('password', null);
    }

    public function admins()
    {
        return $this->members()->where('is_admin', true);
    }

    public function transactions()
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

    public function invitations()
    {
        return $this->hasMany('App\Invitation');
    }

    public function updateBalance(User $member, $amount)
    {
        $this->members()->where('user_id', $member->id)->increment('balance', $amount);
    }

    /**
     * Recalculate balances in a group.
     * For testing.
     */
    public function refreshBalances()
    {
        foreach ($this->members as $member) {
            $balance = 0;
            foreach ($member->buyed as $buyer) {
                if($buyer->purchase->group->id == $this->id){
                    $balance += $buyer->amount;
                }
            }
            foreach ($member->received as $receiver) {
                if($receiver->purchase->group->id == $this->id){
                    $balance -= $receiver->amount;
                }
            }
            $member->member_data->update(['balance' => $balance]);
        }
    }
}
