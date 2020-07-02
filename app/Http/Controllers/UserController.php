<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\User;
use App\Group;

class UserController extends Controller
{

    public function indexGroups(User $user)
    {
        return GroupResource::collection($user->groups);
    }

    public function showGroup(User $user, Group $group)
    {
        if($group->members->contains($user)){
            return /*[
                'data'=> [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'member' => */new MemberResource($group->members->find($user))
                /*]
            ]*/;
        } else {
            return response()->json(['error' => 'This user is not a member of this group.'], 400);
        }
    }

    public function indexTransactions(User $user)
    {
        //TODO return $user->received;
    }

    public function ndexTransactionsInGroup(User $user, Group $group)
    {
        //TODO
    }
}
