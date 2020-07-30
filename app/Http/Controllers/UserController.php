<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\User as UserResource;

use App\User;
use App\Group;

class UserController extends Controller
{
    public function show()
    {
        $user = Auth::guard('api')->user();
        return new UserResource($user);
    }
}
