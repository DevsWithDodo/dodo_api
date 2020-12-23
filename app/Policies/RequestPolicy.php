<?php

namespace App\Policies;

use App\Request;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Request $request)
    {
        return $request->requester->id == $user->id;
    }

    public function delete(User $user, Request $request)
    {
        return $request->requester->id == $user->id;
    }

}
