<?php

namespace App\Policies;

use App\Group;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    public function notGuest(User $user)
    {
        if ($user->is_guest) {
            return Response::deny(__('errors.unauthorized_for_guests'));
        }
    }

    public function join(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->members->count() >= $group->member_limit)
            return Response::deny(__('errors.group_limit_reached'));
        if ($group->members->contains($user))
            return Response::deny(__('errors.already_member'));

        return Response::allow();
    }

    public function boost(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->boosted)
            return Response::deny(__('errors.already_boosted'));
        if ($user->available_boosts <= 0)
            return Response::deny(__('errors.no_boosts'));
        return Response::allow();
    }

    public function view(User $user, Group $group)
    {
        return $group->members->contains($user) ? Response::allow() : Response::deny('user_not_member');
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
            return Response::deny(__('errors.must_be_admin'));
        return Response::allow();
    }

    public function edit_admins(User $user, Group $group)
    {
        $this->notGuest($user);
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny(__('errors.must_be_admin'));
        return Response::allow();
    }

    public function add_guest(User $user, Group $group)
    {
        $this->notGuest($user);
        if ($group->members->count() >= $group->member_limit)
            return Response::deny(__('errors.group_limit_reached'));
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny(__('errors.must_be_admin'));
        return Response::allow();
    }

    public function merge_guest(User $user, Group $group, User $guest)
    {
        $this->notGuest($user);
        if (!($group->member($user->id)->member_data->is_admin))
            return Response::deny(__('errors.must_be_admin'));
        if (!($guest->is_guest))
            return Response::deny(__('errors.must_be_guest'));
        return Response::allow();
    }

    public function viewStatistics(User $user, Group $group)
    {
        return $group->boosted && $group->members->contains($user);
    }
}
