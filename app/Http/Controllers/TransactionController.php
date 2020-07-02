<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions\Purchase;
use App\Transactions\Receiver;
use App\Transactions\Buyer;
use App\Http\Resources\Transaction as TransactionResource;
use App\Group;
use App\User;

class TransactionController extends Controller
{
    public function index(Group $group)
    {
        return TransactionResource::collection($group->transactions);
    }

    public function show(Group $group, Purchase $purchase)
    {
        if($purchase->group_id == $group->id){
            return new TransactionResource($purchase);
        } else {
            return response()->json(['error' => 'This purchase is not belong to this group.'], 400);
        }
    }
}
