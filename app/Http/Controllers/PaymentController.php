<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Rules\IsMember;

use App\Http\Resources\Payment as PaymentResource;

use App\Notifications\PaymentNotification;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user(); //member
        $group = Group::findOrFail($request->group);

        $payments = $group->payments()
            ->where(function ($query) use ($user) {
                return $query->where('taker_id', $user->id)
                    ->orWhere('payer_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->limit($request->limit)
            ->get();
        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $payer = auth('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($request->group)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //The request is valid

        $group = Group::find($request->group);
        $taker = User::find($request->taker_id);

        $payment = Payment::create([
            'amount' => $request->amount,
            'group_id' => $group->id,
            'taker_id' => $taker->id,
            'payer_id' => $payer->id,
            'note' => $request->note ?? null
        ]);
        Cache::forget($group->id . '_balances');
        try {
            $taker->notify(new PaymentNotification($payment));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        return response()->json(new PaymentResource($payment), 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $payer = auth('api')->user();
        $group = $payment->group;
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($group->id)],
            'note' => 'nullable|string|min:1|max:50'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        $payment->update([
            'amount' => $request->amount,
            'note' => $request->note,
            'taker_id' => $request->taker_id
        ]);
        Cache::forget($group->id . '_balances');

        //TODO notify

        return response()->json(new PaymentResource($payment), 200);
    }

    public function delete(Payment $payment)
    {
        Cache::forget($payment->group->id . '_balances');
        $payment->delete();
        //TODO notify
        return response()->json(null, 204);
    }
}
