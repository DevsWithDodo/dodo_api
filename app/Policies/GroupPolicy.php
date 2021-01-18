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
    public function notGuest(User $user)
    {
        if ($user->is_guest) {
            return Response::deny('Unauthorized for guest users.');
        }
    }

    public function join(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->members->count() >= $group->member_limit)
            return Response::deny('The group\'s member limit is reached.');
        if ($group->members->contains($user))
            return Response::deny('You are already a member of this group.');

        return Response::allow();
    }

    public function boost(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->boosted)
            return Response::deny('The group is boosted already.');
        if ($user->available_boosts <= 0)
            return Response::deny('You do not have any boosts available.');
        return Response::allow();
    }

    public function view(User $user, Group $group)
    {
        return $group->members->contains($user);
    }

    public function edit(User $user, Group $group)
    {
        $this->notGuest($user);
        return $group->member($user->id)->member_data->is_admin;
    }

    public function edit_member(User $user, Group $group, $member)
    {
        $this->notGuest($user);
        if ($user->id == $member->id)
            return Response::allow();
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny('You must be an admin.');
        return Response::allow();
    }

    public function edit_admins(User $user, Group $group)
    {
        $this->notGuest($user);
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny('You must be an admin.');
        return Response::allow();
    }

    public function add_guest(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->members->count() >= $group->member_limit)
            return Response::deny('The group\'s member limit is reached.');
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny('You must be an admin.');
        return Response::allow();
    }

    public function merge_guest(User $user, Group $group, User $guest)
    {
        $this->notGuest($user);
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny('You must be an admin');
        if (!($guest->is_guest))
            return Response::deny('The chosen user is not a guest.');
        return Response::allow();
    }
}
