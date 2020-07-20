<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Transactions\Purchase;
use App\Transactions\Receiver;
use App\Transactions\Buyer;
use App\Http\Resources\Transaction as TransactionResource;
use App\Http\Controllers\GroupController;
use App\Group;
use App\User;

class TransactionController extends Controller
{

    public function indexBuyedInGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $user = Auth::guard('api')->user();
        $group = Group::find($request->group_id);

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

    public function indexReceivedInGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::guard('api')->user();
        $group = Group::find($request->group_id);

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

    public function show(Purchase $purchase)
    {
        return new TransactionResource($purchase);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|integer|min:0',
            'buyers' => 'required|array|min:1',
            'buyers.*.user_id' => ['required','exists:users,id', new IsMember($request->group_id)],
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($request->group_id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $request->group_id
        ]);
        foreach ($request->buyers as $buyer_data) {
            $amount = $request->amount/count($request->buyers);
            Buyer::create([
                'amount' => $amount,
                'buyer_id' => $buyer_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            GroupController::updateBalance(Group::find($request->group_id), User::find($buyer_data['user_id']), $amount);
        }
        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            Receiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            GroupController::updateBalance(Group::find($request->group_id), User::find($receiver_data['user_id']), (-1)*$amount);
        }
        return response()->json(new TransactionResource($purchase), 201);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $group = $purchase->group;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'amount' => 'required|integer|min:0',
            'buyers' => 'required|array|min:1',
            'buyers.*.user_id' => ['required','exists:users,id', new IsMember($group->id)],
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required','exists:users,id', new IsMember($group->id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group = $purchase->group;

        //delete associated buyers and receivers
        foreach ($purchase->buyers as $buyer) {
            GroupController::updateBalance($group, $buyer->user, (-1)*$buyer->amount);
            $buyer->delete();
        }
        foreach ($purchase->receivers as $receiver) {
            GroupController::updateBalance($group, $receiver->user, $receiver->amount);
            $receiver->delete();
        }

        //update purchase - with the extortion of updating the timestamps
        $purchase->update([
            'name' => "",
        ]);
        $purchase->update([
            'name' => $request->name
        ]);

        //recreate buyers and receivers
        foreach ($request->buyers as $buyer_data) {
            $amount = $request->amount/count($request->buyers);
            Buyer::create([
                'amount' => $amount,
                'buyer_id' => $buyer_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            GroupController::updateBalance($group, User::find($buyer_data['user_id']), $amount);
        }
        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount/count($request->receivers);
            Receiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            GroupController::updateBalance($group, User::find($receiver_data['user_id']), (-1)*$amount);
        }
        return response()->json(new TransactionResource($purchase), 200);
    }

    public function delete(Purchase $purchase)
    {
        foreach ($purchase->buyers as $buyer) {
            GroupController::updateBalance($purchase->group, $buyer->user, (-1)*$buyer->amount);
            $buyer->delete();
        }
        foreach ($purchase->receivers as $receiver) {
            GroupController::updateBalance($purchase->group, $receiver->user, $receiver->amount);
            $receiver->delete();
        }
        $purchase->delete();

        return response()->json(null, 204);
    }
}
