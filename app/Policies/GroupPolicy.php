<?php

namespace App\Policies;

use App\Group;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class GroupPolicy
{
    use HandlesAuthorization;

    public function join(User $user, Group $group)
    {
        if($user->isGuest())
            Response::deny('$$unauthorized_for_guests$$');
        if($group->members->count() > 20)
            Response::deny('$$member_limit_exceeded$$');
        if($group->members->contains($user))
            Response::deny('$$user_already_a_member$$');

        Response::allow();
    }

    public function edit(User $user, Group $group)
    {
        return $group->members->find($user->id)->member_data->is_admin;
    }

    public function edit_member(User $user, User $member, Group $group)
    {
        return $user->id == $member->id || $group->members->find($user->id)->member_data->is_admin;
    }

    public function edit_admin(User $user, User $admin, Group $group)
    {
        Log::info($user->id);
        return $group->members->find($user->id)->member_data->is_admin && !$admin->isGuest();
    }
}
