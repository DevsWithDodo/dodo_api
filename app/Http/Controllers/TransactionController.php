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
    public function index()
    {
        return TransactionResource::collection(Purchase::all());
    }

    public function show(Purchase $purchase)
    {
        return new TransactionResource($purchase);
    }
}
