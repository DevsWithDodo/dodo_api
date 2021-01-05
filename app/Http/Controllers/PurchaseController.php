<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        $user = auth('api')->user(); //member
        $group = Group::findOrFail($request->group);

        $purchases = $group->purchases()
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('receivers', function ($query) use ($user) {
                        $query->where('receiver_id', $user->id);
                    })
                    ->orWhere('buyer_id', $user->id);
            })
            ->orderBy('purchases.updated_at', 'desc')
            ->limit($request->limit)
            ->get();

        return PurchaseResource::collection($purchases);
    }

    public function store(Request $request)
    {
        $user = auth('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:30',
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', 'exists:users,id', new IsMember($request->group)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        $group = Group::find($request->group);

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $group->id,
            'buyer_id' => $user->id,
            'amount' => $request->amount
        ]);
        $amount_divided = bcdiv($request->amount, count($request->receivers));
        $remainder = bcsub($request->amount, bcmul($amount_divided, count($request->receivers)));
        foreach ($request->receivers as $receiver_data) {
            $receiver = PurchaseReceiver::create([
                'amount' => bcadd($amount_divided, $remainder),
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $remainder = 0;
            try {
                if ($receiver->receiver_id != $user->id)
                    $receiver->user->notify(new ReceiverNotification($receiver));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
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
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        //update receivers
        $purchase->receivers()->delete();

        $amount_divided = bcdiv($request->amount, count($request->receivers));
        $remainder = bcsub($request->amount, bcmul($amount_divided, count($request->receivers)));
        foreach ($request->receivers as $receiver_data) {
            PurchaseReceiver::create([
                'amount' => bcadd($amount_divided, $remainder),
                'receiver_id' => $receiver_data['user_id'],
                'purchase_id' => $purchase->id
            ]);
            $remainder = 0;
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
