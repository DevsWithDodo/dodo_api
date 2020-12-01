<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Rules\IsMember;

use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;
use App\Http\Resources\Purchase as PurchaseResource;

use App\Notifications\ReceiverNotification;
use App\Group;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $group = Group::findOrFail($request->group);

        $ids = $group->purchases()
            ->join('purchase_receivers', 'purchase_receivers.purchase_id', '=', 'purchases.id')
            ->where(function ($query) use ($user) {
                return $query->where('purchases.buyer_id', $user->id)
                    ->orWhere('purchase_receivers.receiver_id', $user->id);
            })
            ->pluck('purchases.id');
        $purchases = Purchase::with('group.members')->whereIn('id', $ids)->orderBy('updated_at', 'desc')->get();
        return PurchaseResource::collection($purchases);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:30',
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', 'exists:users,id', new IsMember($request->group)]
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('name')) abort(400, "26");
            if ($errors->has('group')) abort(400, "23");
            if ($errors->has('amount')) abort(400, "24");
            if ($errors->has('receivers')) abort(400, "20");
            abort(400, "0");
        }

        //the request is valid

        $group = Group::find($request->group);

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $group->id,
            'buyer_id' => $user->id,
            'amount' => $request->amount
        ]);

        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount / count($request->receivers);
            $receiver = PurchaseReceiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);

            try{
                if ($receiver->receiver_id != $user->id)
                    $receiver->user->notify(new ReceiverNotification($receiver));
            } catch(Throwable $e){
                report($e);
            }
        }
        Cache::forget($group->id . '_balances');
        return response()->json(new PurchaseResource($purchase), 201);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $group = $purchase->group;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:30',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', 'exists:users,id', new IsMember($group->id)]
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('name')) abort(400, "26");
            if ($errors->has('amount')) abort(400, "24");
            if ($errors->has('receivers')) abort(400, "20");
            abort(400, "0");
        }

        //the request is valid

        //update receivers
        $purchase->receivers()->delete();
        foreach ($request->receivers as $receiver_data) {
            $amount = $request->amount / count($request->receivers);
            PurchaseReceiver::create([
                'amount' => $amount,
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
        }

        //update purchase
        $purchase->update([
            'name' => $request->name,
            'amount' => $request->amount
        ]);
        $purchase->touch();
        Cache::forget($group->id . '_balances');

        //TODO notify

        return response()->json(new PurchaseResource($purchase), 200);
    }

    public function delete(Purchase $purchase)
    {
        Cache::forget($purchase->group->id . '_balances');
        $purchase->delete();
        //TODO notify
        return response()->json(null, 204);
    }
}
