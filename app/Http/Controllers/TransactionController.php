<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMember;

use App\Transactions\Purchase;
use App\Transactions\Receiver;
use App\Transactions\Buyer;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Controllers\GroupController;

use App\Notifications\ReceiverNotification;
use App\Group;
use App\User;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group = Group::find($request->group);

        $transactions = [];
        foreach ($group->transactions->sortByDesc('created_at') as $purchase) {
            if(($purchase->buyer->user == $user) && ($purchase->receivers->contains('receiver_id', $user->id))){
                $transactions[] = [
                    'type' => 'buyed_received',
                    'data' => new TransactionResource($purchase)
                ];
            } else{
                if($purchase->buyer->user == $user){
                    $transactions[] = [
                        'type' => 'buyed',
                        'data' => new TransactionResource($purchase)
                    ];
                }
                if($purchase->receivers->contains('receiver_id', $user->id)){
                    $transactions[] = [
                        'type' => 'received',
                        'data' => new TransactionResource($purchase)
                    ];
                }
            }
        }
        return new JsonResource($transactions);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:20',
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($request->group)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group = Group::find($request->group);

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $group->id
        ]);

        Buyer::create([
            'amount' => $request->amount,
            'buyer_id' => $user->id,
            'purchase_id' => $purchase->id
        ]);
        $group->updateBalance($user, $request->amount);

        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            $receiver = Receiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $group->updateBalance(User::find($receiver_data['user_id']), (-1)*$amount);
            if($receiver->receiver_id != $user->id){
                $receiver->user->notify(new ReceiverNotification($receiver));
            }
        }
        return response()->json(new TransactionResource($purchase), 201);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $buyer = $purchase->buyer;
        $group = $purchase->group;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:20',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($group->id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        //update buyer
        $group->updateBalance($buyer->user, (-1)*$buyer->amount);
        $buyer->update(['amount' => $request->amount]);
        $group->updateBalance($buyer->user, $buyer->amount);

        //update receivers
        foreach ($purchase->receivers as $receiver) {
            $group->updateBalance($receiver->user, $receiver->amount);
            $receiver->delete();
        }
        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            Receiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $group->updateBalance(User::find($receiver_data['user_id']), (-1)*$amount);
        }

        //update purchase - with the extortion of updating the timestamps
        $purchase->update(['name' => $request->name]);
        $purchase->touch();

        return response()->json(new TransactionResource($purchase), 200);
    }

    public function delete(Purchase $purchase)
    { 
        //delete buyer
        $buyer = $purchase->buyer;
        $purchase->group->updateBalance($buyer->user, (-1)*$buyer->amount);
        $buyer->delete();
        
        //delete receivers
        foreach ($purchase->receivers as $receiver) {
            $purchase->group->updateBalance($receiver->user, $receiver->amount);
            $receiver->delete();
        }

        //delete purchase
        $purchase->delete();

        return response()->json(null, 204);
    }
}
