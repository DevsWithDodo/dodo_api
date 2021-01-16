<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Rules\IsMember;

use App\Transactions\Purchase;
use App\Http\Resources\Purchase as PurchaseResource;

use App\Notifications\ReceiverNotification;
use App\Group;
use App\Transactions\Reactions\PurchaseReaction;

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
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:30',
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', 'exists:users,id', new IsMember($request->group)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $group = Group::find($request->group);
        $this->authorize('member', $group);

        $purchase = Purchase::create([
            'name' => $request->name,
            'group_id' => $group->id,
            'buyer_id' => $user->id,
            'amount' => $request->amount
        ]);
        $purchase->createReceivers(array_map(
            function ($i) {
                return $i['user_id'];
            },
            $request->receivers
        ));

        //notification
        foreach ($purchase->receivers as $receiver) {
            try {
                if ($receiver->user->id != $user->id)
                    $receiver->user->notify(new ReceiverNotification($receiver));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }

        return response()->json(new PurchaseResource($purchase), 201);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorize('update', $purchase);
        $group = $purchase->group;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:30',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', 'exists:users,id', new IsMember($group->id)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        foreach ($purchase->receivers as $receiver) {
            $receiver->delete();
        }
        $purchase->update([
            'name' => $request->name,
            'amount' => $request->amount
        ]);
        $purchase->createReceivers(array_map(
            function ($i) {
                return $i['user_id'];
            },
            $request->receivers
        ));

        $purchase->touch();

        //TODO notify

        return response()->json(new PurchaseResource($purchase), 200);
    }

    public function delete(Purchase $purchase)
    {
        $this->authorize('delete', $purchase);
        $purchase->delete();
        //TODO notify
        return response()->json(null, 204);
    }

    public function reaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_id' => 'required|exists:purchases,id',
            'reaction' => 'required|string|min:1|max:1'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = auth('api')->user();
        $reaction = PurchaseReaction::where('user_id', $user->id)
            ->where('purchase_id', $request->purchase_id)
            ->first();

        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else PurchaseReaction::create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'purchase_id' => $request->purchase_id
        ]);

        return response()->json(null, 204);
    }
}
