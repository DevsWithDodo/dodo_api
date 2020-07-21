<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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
            abort(400, 'User is not a member of this group.');
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group = Group::create($request->all());

        return response()->json(new GroupResource($group), 200);
    }

    public function update(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group->update($request->all());
        return response()->json(new GroupResource($group), 200);
    }

    public function delete(Group $group)
    {
        $group->members()->detach($group->members);
        $group->delete();

        return response()->json(null, 204);
    }

    /* Members */
    public function addMember(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        $group->members()->attach($user, [
            'nickname' => $request->nickname ?? null,
            'is_admin' => $group->members()->count() == 0 //set to true on first member
        ]);
        
        return response()->json(null, 204);
    }

    public function updateMember(Group $group, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nickname' => 'string|min:3',
            'is_admin' => 'boolean'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::guard('api')->user();
        $member = $group->members->findOrFail($user);
        $member->member_data->update($request->all());

        return response()->json(null, 204);

    }

    public function deleteMember(Group $group)
    {
        //TODO
        //Check for last admin
    }
}
