<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\IsMember;
use Carbon\Carbon;

use App\Transactions\Purchase;
use App\Http\Resources\Purchase as PurchaseResource;
use App\Http\Controllers\CurrencyController;

use App\Group;

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
        $group = Group::findOrFail($request->group);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'amount' => 'required|numeric|min:0',
            'buyer_id' => ['nullable', new IsMember($group)],
            'currency' => ['nullable', Rule::in(CurrencyController::CurrencyList())],
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', new IsMember($group)],
            'receivers.*.amount' => 'nullable|numeric|min:0'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $this->authorize('view', $group);
        $buyer_id = $request->buyer_id ?? auth('api')->user()->id;
        $currency = $request->currency ?? $group->currency;
        $amount = CurrencyController::exchangeCurrency($currency, $group->currency, $request->amount);

        $purchase_data = $request->only(['name', 'receivers']);
        $purchase_data['buyer_id'] = $buyer_id;
        $purchase_data['group_id'] = $group->id;
        $purchase_data['amount'] = $amount;
        $purchase_data['original_currency'] = $currency;

        Purchase::createWithReceivers($purchase_data);

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

        $purchase = Purchase::find($request->purchase_id);
        $user = auth('api')->user();
        $reaction = $purchase->reactions()
            ->where('user_id', $user->id)
            ->first();

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else{
            $purchase->reactions()->create([
                'reaction' => $request->reaction,
                'user_id' => $user->id,
                'group_id' => $purchase->group_id
            ]);
        }

        return response()->json(null, 204);
    }
}
