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
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            return response()->json(['error' => 'User is not a member of this group'], 400);
        }

        $payments = [];
        foreach ($group->payments as $payment) {
            if(($payment->taker == $user) || ($payment->payer == $user)){
                $payments[] = new PaymentResource($payment);
            }
        }
        return $payments;
    }

    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|integer|min:0',
            'payer_id' => ['required','exists:users,id', new IsMember($request->group_id)],
            'taker_id' => ['required','exists:users,id', 'different:payer_id', new IsMember($request->group_id)]
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
            'payer_id' => ['required','exists:users,id', new IsMember($request->group_id)],
            'taker_id' => ['required','exists:users,id', 'different:payer_id', new IsMember($request->group_id)]
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
