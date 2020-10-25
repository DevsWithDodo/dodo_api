<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMember;

use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;
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
            return response()->json(['error' => 0], 400);
        }
        $group = Group::find($request->group);

        $transactions = [];
        foreach ($group->transactions->sortByDesc('created_at') as $purchase) {
            if(($purchase->buyer_id == $user->id) && ($purchase->receivers->contains('receiver_id', $user->id))){
                $transactions[] = [
                    'type' => 'buyed_received',
                    'data' => new TransactionResource($purchase)
                ];
            } else{
                if($purchase->buyer_id == $user->id){
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
            'name' => 'required|string|min:1|max:30',
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($request->group)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => 0], 400);
        }

        $group = Group::find($request->group);

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $group->id,
            'buyer_id' => $user->id,
            'amount' => $request->amount
        ]);

        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            $receiver = PurchaseReceiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
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
            'name' => 'required|string|min:1|max:30',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($group->id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => 0], 400);
        }

        //update receivers
        $purchase->receivers()->delete();
        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            PurchaseReceiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $group->updateBalance(User::find($receiver_data['user_id']), (-1)*$amount);
        }

        //update purchase - with the extortion of updating the timestamps
        $purchase->update([
            'name' => $request->name,
            'amount' => $request->amount
        ]);
        $purchase->touch();

        return response()->json(new TransactionResource($purchase), 200);
    }

    public function delete(Purchase $purchase)
    {
        $purchase->delete();

        return response()->json(null, 204);
    }
}
