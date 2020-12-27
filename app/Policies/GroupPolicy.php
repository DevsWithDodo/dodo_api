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
            return Response::deny('$$unauthorized_for_guests$$');
        if($group->members->count() >= 20)
            return Response::deny('$$member_limit_exceeded$$');
        if($group->members->contains($user))
            return Response::deny('$$user_already_a_member$$');

        return Response::allow();
    }

    public function edit(User $user, Group $group)
    {
        return $group->members->find($user->id)->member_data->is_admin;
    }

    public function edit_member(User $user, Group $group, $member)
    {
        return $user->id == $member->id || $group->members->find($user->id)->member_data->is_admin;
    }

    public function edit_admin(User $user, Group $group, $admin)
    {
        return $group->members->find($user->id)->member_data->is_admin && !$admin->isGuest();
    }

    public function add_guest(User $user, Group $group)
    {
        if($group->members->count() > 20)
            return Response::deny('$$member_limit_exceeded$$');
        return $group->members->find($user->id)->member_data->is_admin;
    }
}
