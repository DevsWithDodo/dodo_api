<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\User as UserResource;

use App\User;
use App\Group;

class UserController extends Controller
{
    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function balance()
    {
        $user = Auth::guard('api')->user();
        $balance=0;
        foreach ($user->groups as $group) {
            $balance += $group->member_data->balance;
        }
        return response()->json(floatval($balance));
    }
}
