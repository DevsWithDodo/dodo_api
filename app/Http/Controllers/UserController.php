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
use App\Http\Resources\Transaction as TransactionResource;

use App\User;
use App\Group;
use App\Transactions\Purchase;
use App\Transactions\Buyer;
use App\Transactions\Receiver;
use App\Transactions\Payment;
//use SplPriorityQueue; //priority queue

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
        $balance = $group->members->findOrFail($user)->member_data->balance;
        return response()->json($balance);
    }

 /*    public function indexHistory(Group $group) 
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            return response()->json(['error' => 'User is not a member of this group'], 400);
        }

        $transactions = [];
        foreach ($group->transactions as $purchase) {
            if($purchase->buyer->user == $user){
                $transactions[] = [
                    'type' => 'buyed',
                    'data' => new TransactionResource($purchase)
                ];
            }
            foreach($purchase->receivers as $receiver){
                if($receiver->user == $user){
                    $transactions[] = [
                        'type' => 'received',
                        'data' => new TransactionResource($purchase)
                    ];
                }
            }
        }

        return $transactions; */
        
/*         $transactions = new SplPriorityQueue();
        foreach ($user->buyed as $buyer){
            //if($buyer->purchase->group == $group){
                $transaction['type'] = 'buyed';
                $transaction['data'] = new TransactionResource($buyer->purchase);
                //$transaction['data']['amount'] = $buyer->amount;
                //unset($transaction['data']['group']);
                $transactions->insert($transaction, $transaction['data']->created_at);
            //}
        }
        foreach ($user->received as $receiver) {
            if($receiver->purchase->group == $group){
                $transaction['type'] = 'received';
                $transaction['data'] = new TransactionResource($receiver->purchase);
                $transaction['data']['amount'] = $receiver->amount;
                unset($transaction['data']['group']);
                $transactions->insert($transaction, $transaction['data']->created_at);
            }
        }
        $payments = new SplPriorityQueue();
        foreach ($user->payed as $payment) {
            if($payment->group == $group){
                $transaction['data'] = new PaymentResource($payment);
                $transaction['type'] = 'payed';
                $payments->insert($transaction, $transaction['data']->created_at);
            }
        }
        foreach ($user->taken as $payment) {
            if($payment->group == $group){
                $transaction['data'] = new PaymentResource($payment);
                $transaction['type'] = 'taken';
                $payments->insert($transaction, $transaction['data']->created_at);
            }
        }

        $array = [];
        foreach($transactions as $transaction){
            $array['transactions'][] = $transaction;
        }
        foreach($payments as $payment){
            $array['payments'][] = $payment;
        } */
        //return new JsonResource($array);
    //}

//}
