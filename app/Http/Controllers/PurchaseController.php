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
        $group = Group::findOrFail($request->group);
        $this->authorize('member', $group);

        $validator = Validator::make($request->all(), [
            'from_date' => 'nullable|date',
            'until_date' => 'nullable|date',
            'category' => ['nullable', Rule::in($group->categories)],
            'user_id' => ['nullable', new IsMember($group)],
            'limit' => 'nullable|numeric|min:0'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date ?? '2000-01-01');
        $until_date = Carbon::parse($request->until_date ?? now())->addDay();

        $user_id = $request->user_id ?? auth('api')->user()->id;

        $purchases = $group->purchases()
            ->whereBetween('updated_at', [$from_date,$until_date])
            ->where(function ($query) use ($user_id) {
                $query
                    ->whereHas('receivers', function ($query) use ($user_id) {
                        $query->where('receiver_id', $user_id);
                    })
                    ->orWhere('buyer_id', $user_id);
            });

        if ($request->category) $purchases = $purchases->where('category', $request->category);

        $purchases = $purchases
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
            'category' => ['nullable', Rule::in($group->categories)],
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', new IsMember($group)],
            'receivers.*.amount' => 'nullable|numeric|min:0'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $this->authorize('member', $group);

        $purchase_data = $request->only(['name', 'amount', 'category', 'receivers']);
        $purchase_data['buyer_id'] = $request->buyer_id ?? auth('api')->user()->id;
        $purchase_data['group_id'] = $group->id;
        $purchase_data['group_currency'] = $group->currency;
        $purchase_data['original_currency'] = $request->currency ?? $group->currency;

        Purchase::createWithReceivers($purchase_data);

        return response()->json(null, 204);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorize('member', $purchase->group);
        $group = $purchase->group;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:50',
            'amount' => 'required|numeric|min:0',
            'buyer_id' => ['nullable', new IsMember($group)],
            'currency' => ['nullable', Rule::in(CurrencyController::CurrencyList())],
            'category' => ['nullable', Rule::in($group->categories)],
            'receivers' => 'required|array|min:1',
            'receivers.*.user_id' => ['required', new IsMember($group)],
            'receivers.*.amount' => 'nullable|numeric|min:0'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $purchase_data = $request->only(['name', 'amount', 'category', 'receivers']);
        $purchase_data['buyer_id'] = $request->buyer_id ?? auth('api')->user()->id;
        $purchase_data['group_id'] = $group->id;
        $purchase_data['group_currency'] = $group->currency;
        $purchase_data['original_currency'] = $request->currency ?? $group->currency;;

        $purchase->updateWithReceivers($purchase_data);

        return response()->json(null, 204);
    }

    public function delete(Purchase $purchase)
    {
        $this->authorize('member', $purchase->group);
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
        $this->authorize('member', $purchase->group);
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
