<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
    public function show(User $user)
    {
        return $user;
    }

    public function balance()
    {
        $user = Auth::guard('api')->user();
        $balance=0;
        foreach ($user->groups as $group) {
            $balance += $group->member_data->balance;
        }
        return response()->json($balance);
    }

    public function balanceInGroup(Group $group)
    {
        $user = Auth::guard('api')->user();
        $balance = $group->members->find($user)->member_data->balance;
        return response()->json($balance);
    }

    public function indexHistory() 
    //could be slow
    //only last x can be enough
    {
        $user = Auth::guard('api')->user();
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

}
