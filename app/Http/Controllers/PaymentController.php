<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Rules\IsMember;

use App\Http\Resources\Payment as PaymentResource;

use App\Notifications\PaymentNotification;
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
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($request->group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $group = Group::findOrFail($request->group);
        $taker = User::findOrFail($request->taker_id);

        $this->authorize('member', $group);

        $payment = Payment::create([
            'amount' => $request->amount,
            'group_id' => $group->id,
            'taker_id' => $taker->id,
            'payer_id' => $payer->id,
            'note' => $request->note ?? null
        ]);
        try {
            $taker->notify(new PaymentNotification($payment));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        return response()->json(new PaymentResource($payment), 201);
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);
        $payer = auth('api')->user();
        $group = $payment->group;
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($group->id)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $payment->update([
            'amount' => $request->amount,
            'note' => $request->note,
            'taker_id' => $request->taker_id
        ]);

        //TODO notify

        return response()->json(new PaymentResource($payment), 200);
    }

    public function delete(Payment $payment)
    {
        $this->authorize('delete', $payment);
        $payment->delete();
        //TODO notify
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
