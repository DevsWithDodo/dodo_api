<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsMember;
use Carbon\Carbon;

use App\Transactions\Purchase;
use App\Http\Resources\Purchase as PurchaseResource;

use App\Group;
use App\Transactions\Reactions\PurchaseReaction;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $group = Group::findOrFail($request->group);
        $this->authorize('view', $group);

        $from_date  = Carbon::parse($request->from_date ?? '2000-01-01');
        $until_date = Carbon::parse($request->until_date ?? now())->addDay();

        $purchases = $group->purchases()
            ->whereBetween('updated_at', [$from_date,$until_date])
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('receivers', function ($query) use ($user) {
                        $query->where('receiver_id', $user->id);
                    })
                    ->orWhere('buyer_id', $user->id);
            })
            ->orderBy('purchases.updated_at', 'desc')
            ->limit($request->limit)
            ->with('receivers')
            ->with('reactions')
            ->get();
        return PurchaseResource::collection($purchases);
    }

    public function store(Request $request)
    {
        $user = auth('api')->user();
        $group = Group::findOrFail($request->group);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', new IsMember($group)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $this->authorize('view', $group);
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

        return response()->json(null, 204);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorize('update', $purchase);
        $group = $purchase->group;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'amount' => 'required|numeric|min:0',
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', new IsMember($group)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $purchase->update([
            'name' => $request->name,
            'amount' => $request->amount
        ]);

        $new_receivers = array_map(function ($i) {
            return $i['user_id'];
        }, $request->receivers);

        $purchase->createReceivers($new_receivers);
        $purchase->touch();

        return response()->json(null, 204);
    }

    public function delete(Purchase $purchase)
    {
        $this->authorize('delete', $purchase);
        $purchase->delete();
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

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else PurchaseReaction::create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'purchase_id' => $request->purchase_id,
            'group_id' => Purchase::find($request->purchase_id)->group_id
        ]);

        return response()->json(null, 204);
    }
}
