<?php

namespace App\Policies;

use App\Request;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class RequestPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Request $request)
    {
        return $request->requester_id == $user->id;
    }

    public function delete(User $user, Request $request)
    {
        return $request->requester_id == $user->id || $request->group->members->contains($user);
    }
}
