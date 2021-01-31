<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsMember;

use App\Http\Resources\Payment as PaymentResource;

use App\Transactions\Reactions\PaymentReaction;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $group = Group::findOrFail($request->group);

        $payments = $group->payments()
            ->where(function ($query) use ($user) {
                return $query->where('taker_id', $user->id)
                    ->orWhere('payer_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->with('reactions')
            ->limit($request->limit)
            ->get();
        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $payer = auth('api')->user();
        $group = Group::findOrFail($request->group);
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'not_in:' . $payer->id, new IsMember($group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $taker = User::findOrFail($request->taker_id);

        $this->authorize('view', $group);

        Payment::create([
            'amount' => $request->amount,
            'group_id' => $group->id,
            'taker_id' => $taker->id,
            'payer_id' => $payer->id,
            'note' => $request->note ?? null
        ]);
        return response()->json(null, 204);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);
        $payer = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($payment->group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $payment->update($request->only('amount', 'taker_id', 'note'));

        return response()->json(null, 204);
    }

    public function delete(Payment $payment)
    {
        $this->authorize('delete', $payment);
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
        $reaction = PaymentReaction::where('user_id', $user->id)
            ->where('payment_id', $request->payment_id)
            ->first();

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else PaymentReaction::create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'payment_id' => $request->payment_id,
            'group_id' => Payment::find($request->payment_id)->group_id
        ]);

        return response()->json(null, 204);
    }
}
