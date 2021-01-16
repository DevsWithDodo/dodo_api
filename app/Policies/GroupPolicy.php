<?php

namespace App\Policies;

use App\Group;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user)
    {
        if ($user->isGuest()) {
            return Response::deny('Unauthorized for guest users.');
        }
    }

    public function join(User $user, Group $group)
    {
        if ($group->members->count() >= 20)
            return Response::deny('The group\'s member limit reached.');
        if ($group->members->contains($user))
            return Response::deny('You are already a member of this group.');

        return Response::allow();
    }

    public function view(User $user, Group $group)
    {
        return $group->members->contains($user);
    }

    public function edit(User $user, Group $group)
    {
        return $group->member($user->id)->member_data->is_admin;
    }

    public function edit_member(User $user, Group $group, $member)
    {
        return $user->id == $member->id
            || $group->member($user->id)->member_data->is_admin;
    }

    public function edit_admins(User $user, Group $group)
    {
        return $group->member($user->id)->member_data->is_admin;
    }

    public function add_guest(User $user, Group $group)
    {
        if ($group->members->count() > 20)
            return Response::deny('The group\'s member limit reached.');
        return $group->member($user->id)->member_data->is_admin;
    }

    public function merge_guest(User $user, Group $group, User $guest)
    {
        return $group->member($user->id)->member_data->is_admin
            && $guest->isGuest();
    }
}
