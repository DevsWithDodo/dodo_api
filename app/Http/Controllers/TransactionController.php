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

use App\Group;
use App\User;

class TransactionController extends Controller
{

    public function index(Request $request, Group $group)
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            return response()->json(['error' => 'User is not a member of this group'], 400);
        }

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
        return $transactions;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:20',
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($request->group_id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::guard('api')->user();
        $group = Group::find($request->group_id);
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $request->group_id
        ]);

        Buyer::create([
            'amount' => $request->amount,
            'buyer_id' => $user->id,
            'purchase_id' => $purchase->id
        ]);
        $group->updateBalance($user, $request->amount);

        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            Receiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $group->updateBalance(User::find($receiver_data['user_id']), (-1)*$amount);
        }
        return response()->json(new TransactionResource($purchase), 201);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $user = Auth::guard('api')->user();
        if($user == $purchase->buyer->user){
            $group = $purchase->group;
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:20',
                'amount' => 'required|numeric|min:0',
                'receivers' => 'required|array|min:1',
                'receivers.*.user_id' => ['required','exists:users,id', new IsMember($group->id)]
            ]);
            if($validator->fails()){
                return response()->json(['error' => $validator->errors()], 400);
            }

            //update buyer
            $buyer = $purchase->buyer;
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
        } else {
            return response()->json(['error' => 'User is not the buyer of the transaction'], 400);
        }
        
    }

    public function delete(Purchase $purchase)
    {
        $user = Auth::guard('api')->user();
        if($user == $purchase->buyer->user){
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
        } else {
            return response()->json(['error' => 'User is not the buyer of the transaction'], 400);
        }
    }
}
