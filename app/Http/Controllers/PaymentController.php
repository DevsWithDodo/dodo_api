<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\Payment as PaymentResource;
use App\Http\Controllers\GroupController;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentController extends Controller
{
    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|integer|min:0',
            'payer_id' => 'required|exists:users,id',
            'taker_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $payment = Payment::create($request->all());
        $group = Group::find($request->group_id);
        GroupController::updateBalance($group, User::find($request->payer_id), $request->amount);
        GroupController::updateBalance($group, User::find($request->taker_id), (-1)*$request->amount);

        return response()->json(new PaymentResource($payment), 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:0',
            'payer_id' => 'required|exists:users,id',
            'taker_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group = $payment->group;
        GroupController::updateBalance($group, $payment->payer, (-1)*$payment->amount);
        GroupController::updateBalance($group, $payment->taker, $payment->amount);

        $payment->update($request->all());
        GroupController::updateBalance($group, User::find($request->payer_id), $request->amount);
        GroupController::updateBalance($group, User::find($request->taker_id), (-1)*$request->amount);

        return response()->json(new PaymentResource($payment), 200);
    }

    public function delete(Payment $payment)
    {
        $group = $payment->group;
        GroupController::updateBalance($group, $payment->payer, (-1)*$payment->amount);
        GroupController::updateBalance($group, $payment->taker, $payment->amount);
        $payment->delete();

        return response()->json(null, 204);
    }
}