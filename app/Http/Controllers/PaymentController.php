<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Rules\IsMember;

use App\Http\Resources\Payment as PaymentResource;
use App\Http\Controllers\GroupController;

use App\Notifications\PaymentNotification;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $group = Group::findOrFail($request->group);

        $payments = $group->payments()
            ->where(function ($query) use ($user) {
                return $query->where('taker_id', $user->id)
                    ->orWhere('payer_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();
        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $payer = Auth::guard('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($request->group)],
            'note' => 'nullable|string|min:1|max:25'
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['id' => Auth::guard('api')->user()->id, 'function' => 'PaymentController@store']);
            abort(400, "0");
        }

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

        if (env('NOTIFICATION_ACTIVE'))
            $taker->notify(new PaymentNotification($payment));

        return response()->json(new PaymentResource($payment), 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $payer = Auth::guard('api')->user();
        $group = $payment->group;
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required', 'exists:users,id', 'not_in:' . $payer->id, new IsMember($group->id)],
            'note' => 'nullable|string|min:1|max:25'
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors(), ['id' => Auth::guard('api')->user()->id, 'function' => 'PaymentController@update']);
            abort(400, "0");
        }

        $payment->update([
            'amount' => $request->amount,
            'note' => $request->note,
            'taker_id' => $request->taker_id
        ]);
        Cache::forget($group->id . '_balances');

        return response()->json(new PaymentResource($payment), 200);
    }

    public function delete(Payment $payment)
    {
        Cache::forget($payment->group->id . '_balances');
        $payment->delete();
        return response()->json(null, 204);
    }
}
