<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Payment as PaymentResource;
use App\Http\Resources\Member as MemberResource;
use App\Http\Resources\Purchase as PurchaseResource;
use App\User;
use App\Group;
use App\Transactions\Purchase;
use App\Transactions\Buyer;
use App\Transactions\Receiver;
use App\Transactions\Payment;
use SplPriorityQueue; //priority queue

class UserController extends Controller
{
    public function showById(User $user)
    {
        return $user;
    }

    public function showByMail($email)
    {
        $user = User::firstWhere('email', $email);
        return $user;
    }

    /* Balance getters */

    public function balance(User $user)
    {
        $balance=0;
        foreach ($user->groups as $group) {
            $balance += $group->member_data->balance;
        }
        return response()->json($balance);
    }

    public function balanceInGroup(User $user, Group $group)
    {
        $balance = $group->members->find($user)->member_data->balance;
        return response()->json($balance);
    }

    /* Group getters */

    public function indexGroups(User $user)
    {
        return GroupResource::collection($user->groups);
    }

    public function showGroup(User $user, Group $group)
    {
        if($group->members->contains($user)){
            return new JsonResource([
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'member' => new MemberResource($group->members->find($user))
                ]);
        } else {
            return response()->json(['error' => 'This user is not a member of this group.'], 400);
        }
    }

    /* Transaction getters */

    public function indexHistory(User $user) 
    //could be slow
    //only last x can be enough
    {
        $transactions = new SplPriorityQueue();
        foreach ($user->buyed as $buyer){
            $transaction['type'] = 'buyed';
            $transaction['data'] = new PurchaseResource($buyer->purchase);
            $transaction['data']['group_name'] = $buyer->purchase->group->name;
            $transaction['data']['amount'] = $buyer->amount;
            unset($transaction['data']['group']);
            $transactions->insert($transaction, $transaction['data']->created_at);
        }
        foreach ($user->received as $receiver) {
            $transaction['type'] = 'received';
            $transaction['data'] = new PurchaseResource($receiver->purchase);
            $transaction['data']['group_name'] = $receiver->purchase->group->name;
            $transaction['data']['amount'] = $receiver->amount;
            unset($transaction['data']['group']);
            $transactions->insert($transaction, $transaction['data']->created_at);
        }
        foreach ($user->payed as $payment) {
            $transaction['data'] = new PaymentResource($payment);
            $transaction['type'] = 'payed';
            $transactions->insert($transaction, $transaction['data']->created_at);
        }
        foreach ($user->taken as $payment) {
            $transaction['data'] = new PaymentResource($payment);
            $transaction['type'] = 'taken';
            $transactions->insert($transaction, $transaction['data']->created_at);
        }

        $array = [];
        foreach($transactions as $transaction){
            $array[] = $transaction;
        }
        return new JsonResource($array);
    }

    public function indexTransactionsBuyedInGroup(User $user, Group $group)
    {
        $transactions = [];
        foreach ($user->buyed as $buyer){
            if($buyer->purchase->group == $group){
                $transaction = $buyer->purchase;
                $transaction['amount'] = $buyer->amount;
                unset($transaction['group']);
                $transactions[] = $transaction;
            }
        }
        return new JsonResource($transactions);
    }

    public function indexTransactionsReceivedInGroup(User $user, Group $group)
    {
        $transactions = [];
        foreach ($user->received as $receiver){
            if($receiver->purchase->group == $group){
                $transaction = $receiver->purchase;
                $transaction['amount'] = $receiver->amount;
                unset($transaction['group']);
                $transactions[] = $transaction;
            }
        }
        return new JsonResource($transactions);
    }

    /* Payment getters */

    public function indexPaymentsPayedInGroup(User $user, Group $group)
    {
        return PaymentResource::collection($user->payed->where('group_id', $group->id));
    }

    public function indexPaymentsTakenInGroup(User $user, Group $group)
    {
        return PaymentResource::collection($user->taken->where('group_id', $group->id));
    }
}
