<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMember;

use App\Http\Resources\Payment as PaymentResource;
use App\Http\Controllers\GroupController;

use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
{
    public function index(Request $request, Group $group)
    {
        $user = Auth::guard('api')->user(); //member

        return PaymentResource::collection(
            $group->payments()
                ->where('taker_id', $user->id)
                ->orWhere('payer_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get());
    }

    public function store(Request $request)
    {
        $payer = Auth::guard('api')->user(); //member
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required','exists:users,id', 'not_in:'.$payer->id, new IsMember($request->group) ],
            'note' => 'nullable|string|min:1'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
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
        
        $group->updateBalance($payer, $request->amount);
        $group->updateBalance($taker, (-1)*$request->amount);

        return response()->json(new PaymentResource($payment), 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $payer = Auth::guard('api')->user();
        $group = $payment->group;
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required','exists:users,id', 'not_in:'.$payer->id, new IsMember($group->id)],
            'note' => 'nullable|string|min:1'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group->updateBalance($payment->payer, (-1)*$payment->amount);
        $group->updateBalance($payment->taker, $payment->amount);

        $payment->update($request->all());
        $group->updateBalance($payment->payer, $request->amount);
        $group->updateBalance($payment->taker, (-1)*$request->amount);

        return response()->json(new PaymentResource($payment), 200);
    }

    public function delete(Payment $payment)
    {
        $group = $payment->group;
        $group->updateBalance($payment->payer, (-1)*$payment->amount);
        $group->updateBalance($payment->taker, $payment->amount);
        $payment->delete();

        return response()->json(null, 204);
    }
}
