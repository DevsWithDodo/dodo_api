<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions\Purchase;
use App\Transactions\Receiver;
use App\Transactions\Buyer;
use App\User;

class TransactionController extends Controller
{
    public function index(Group $group)
    {
        return Purchase::all()->where('group_id', $group->id);
    }
}
