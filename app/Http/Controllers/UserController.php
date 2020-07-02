<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\User;
use App\Group;
use App\Transactions\Purchase;
use App\Transactions\Buyer;
use App\Transactions\Receiver;
use SplPriorityQueue; //priority queue

class UserController extends Controller
{

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

    public function indexTransactions(User $user)
    {
        $transactions = new SplPriorityQueue();
        foreach ($user->buyed as $buyer){
            $transaction = $buyer->purchase;
            $transaction['group_name'] = $buyer->purchase->group->name;
            $transaction['type'] = 'buyed';
            $transaction['amount'] = $buyer->amount;
            unset($transaction['group']);
            $transactions->insert($transaction, $transaction->created_at);
        }
        foreach ($user->received as $receiver) {
            $transaction = $receiver->purchase;
            $transaction['group_name'] = $receiver->purchase->group->name;
            $transaction['type'] = 'received';
            $transaction['amount'] = $receiver->amount;
            unset($transaction['group']);
            $transactions->insert($transaction, $transaction->created_at);
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
}
