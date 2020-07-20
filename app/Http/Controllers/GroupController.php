<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Group as GroupResource;
use App\User;
use App\Group;

class GroupController extends Controller
{
    public static function refreshBalances(Group $group)
    {
        foreach ($group->members as $member) {
            $balance = 0;
            foreach ($member->buyed as $buyer) {
                if($buyer->purchase->group->id == $group->id){
                    $balance += $buyer->amount;
                }
            }
            foreach ($member->received as $receiver) {
                if($receiver->purchase->group->id == $group->id){
                    $balance -= $receiver->amount;
                }
            }
            $member->member_data->update(['balance' => $balance]);
        }
    }

    public static function updateBalance(Group $group, User $user, $amount)
    {
        $member = $group->members->find($user);
        if($member == null){
            //TODO
        }
        $old_balance = $member->member_data->balance;
        $member->member_data->update(['balance' => $old_balance + $amount]);
    }

    //List all group
    /* public function index()
    {
        return GroupResource::collection(Group::all());
    } */

    public function index()
    {
        $user = Auth::guard('api')->user();
        return GroupResource::collection($user->groups);
    }
    
    //Return a group with its members 
    public function show(Group $group)
    {
        return new GroupResource($group);
    }
    
    /* public function show(Group $group)
    {
        $user = Auth::guard('api')->user();
        if($group->members->contains($user)){
            return new JsonResource([
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'member' => new MemberResource($group->members->find($user))
                ]);
        } else {
            return response()->json(['error' => 'This user is not a member of this group.'], 400);
        }
    } */

    public function createGroup(Request $request)
    {
        //TODO
    }
}
