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
        foreach ($group->payments->sortByDesc('created_at') as $payment) {
            if(($payment->taker == $user) || ($payment->payer == $user)){
                $payments[] = new PaymentResource($payment);
            }
        }
        return $payments;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0',
            'taker_id' => ['required','exists:users,id', new IsMember($request->group_id)],
            'note' => 'string'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group = Group::find($request->group_id);
        $payer = Auth::guard('api')->user();
        $taker = User::find($request->taker_id);
        
        if(Group::find($request->group_id)->members->find($payer->id) == null){
            return response()->json(['error' => 'User is not a member of this group'], 400);
        }
        if($payer == $taker) {
            return response()->json(['error' => 'Payer and taker cannot be the same.'],400);
        }

        $payment = Payment::create([
            'amount' => $request->amount,
            'group_id' => $request->group_id,
            'taker_id' => $taker->id,
            'payer_id' => $payer->id,
            'note' => $request->note ?? null
        ]);
        
        GroupController::updateBalance($group, $payer, $request->amount);
        GroupController::updateBalance($group, $taker, (-1)*$request->amount);

        return response()->json(new PaymentResource($payment), 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $user = Auth::guard('api')->user();
        if($user == $payment->payer){
            $group = $payment->group;
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
                'taker_id' => ['required','exists:users,id', new IsMember($group->id)],
                'note' => 'string'
            ]);
            if($validator->fails()){
                return response()->json(['error' => $validator->errors()], 400);
            }

            if($user == User::find($request->taker_id)) {
                return response()->json(['error' => 'Payer and taker cannot be the same.'],400);
            }

            GroupController::updateBalance($group, $payment->payer, (-1)*$payment->amount);
            GroupController::updateBalance($group, $payment->taker, $payment->amount);

            $payment->update($request->all());
            GroupController::updateBalance($group, $payment->payer, $request->amount);
            GroupController::updateBalance($group, $payment->taker, (-1)*$request->amount);

            return response()->json(new PaymentResource($payment), 200);
        } else {
            return response()->json(['error' => 'User is not the payer of the payment'], 400);
        }
    }

    public function delete(Payment $payment)
    {
        $user = Auth::guard('api')->user();
        if($user == $payment->payer){
            $group = $payment->group;
            GroupController::updateBalance($group, $payment->payer, (-1)*$payment->amount);
            GroupController::updateBalance($group, $payment->taker, $payment->amount);
            $payment->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'User is not the payer of the payment'], 400);
        }
    }
}
