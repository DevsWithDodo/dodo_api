<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\IsMember;
use Carbon\Carbon;

use App\Http\Resources\Payment as PaymentResource;

use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
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

        $payments = $group->payments()
            ->whereBetween('updated_at', [$from_date,$until_date])
            ->where(function ($query) use ($user_id) {
                return $query->where('taker_id', $user_id)
                    ->orWhere('payer_id', $user_id);
            });

        if ($request->category) $payments = $payments->where('category', $request->category);

        $payments = $payments
            ->orderBy('updated_at', 'desc')
            ->with('reactions')
            ->limit($request->limit)
            ->get();
        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $group = Group::findOrFail($request->group);
        $validator = Validator::make($request->all(), [
            'payer_id' => ['nullable', new IsMember($group)],
            'currency' => ['nullable', Rule::in(CurrencyController::CurrencyList())],
            'category' => ['nullable', Rule::in($group->categories)],
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', new IsMember($group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $this->authorize('member', $group);

        $currency = $request->currency ?? $group->currency;
        Payment::create([
            'amount' => CurrencyController::exchangeCurrency($currency, $group->currency, $request->amount),
            'original_amount' => $request->amount,
            'original_currency' => $currency,
            'group_id' => $group->id,
            'taker_id' => $request->taker_id,
            'payer_id' => $request->payer_id ?? auth('api')->user()->id,
            'category' => $request->category,
            'note' => $request->note ?? null
        ]);
        return response()->json(null, 204);
    }

    public function update(Request $request, Payment $payment)
    {
        $group = $payment->group;
        $this->authorize('member', $group);
        $validator = Validator::make($request->all(), [
            'payer_id' => ['nullable', new IsMember($group)],
            'currency' => ['nullable', Rule::in(CurrencyController::CurrencyList())],
            'category' => ['nullable', Rule::in($group->categories)],
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', new IsMember($group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $currency = $request->currency ?? $group->currency;
        $payment->update([
            'amount' => CurrencyController::exchangeCurrency($currency, $group->currency, $request->amount),
            'original_amount' => $request->amount,
            'original_currency' => $currency,
            'taker_id' => $request->taker_id,
            'payer_id' => $request->payer_id ?? auth('api')->user()->id,
            'category' => $request->category,
            'note' => $request->note ?? null
        ]);

        return response()->json(null, 204);
    }

    public function delete(Payment $payment)
    {
        $this->authorize('member', $payment->group);
        $payment->delete();
        return response()->json(null, 204);
    }

    public function reaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
            'reaction' => 'required|string|min:1|max:1'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = auth('api')->user();
        $payment = Payment::find($request->payment_id);
        $this->authorize('member', $payment->group);
        $reaction = $payment->reactions()
            ->where('user_id', $user->id)
            ->first();

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else $payment->reactions()->create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'group_id' => $payment->group_id
        ]);

        return response()->json(null, 204);
    }
}
