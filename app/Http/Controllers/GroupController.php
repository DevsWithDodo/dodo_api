<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMember;

use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
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
        //TODO move this to model
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        $old_balance = $member->member_data->balance;
        $member->member_data->update(['balance' => $old_balance + $amount]);
    }

    public function index()
    {
        $user = Auth::guard('api')->user();
        return GroupResource::collection($user->groups);
    }
     
    public function show(Group $group)
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        return new GroupResource($group);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:3|max:20',
            'member_nickname' => 'string|min:3|max:15'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group = Group::create(['name' => $request->group_name]);

        $group->members()->attach($user, [
            'nickname' => $request->member_nickname ?? null,
            'is_admin' => true //set to true on first member
        ]);

        return response()->json(new GroupResource($group), 201);
    }

    public function update(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:20',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        if($member->member_data->is_admin){
            $group->update($request->all());
            return response()->json(new GroupResource($group), 200);
        } else {
            return response()->json(['error' => 'User is not an admin'], 400);
        }
    }

    public function delete(Group $group)
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        if($member->member_data->is_admin){
            $group->members()->detach($group->members);
            $group->delete();
    
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'User is not an admin'], 400);
        }

    }

    /* Members */
    public function addMember(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        if($group->members->count()==20){
            return response()->json(['error' => 'Group member limit reached'], 400);
        }
        $validator = Validator::make($request->all(), [
            'nickname' => 'string|min:3|max:15',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        if($group->members->find($user) == null){
            $group->members()->attach($user, [
                'nickname' => $request->nickname ?? null,
                'is_admin' => false
            ]);
        } else {
            return response()->json(['error' => 'The user is already a member in this group'], 400);
        }
        
        return response()->json(null, 204);
    }

    public function updateMember(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        $validator = Validator::make($request->all(), [
            'member_id' => ['required','exists:users,id', new IsMember($group->id)],
            'nickname' => 'string|min:3|max:15',
            'is_admin' => 'boolean'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        if($member == $group->members->find($request->member_id)){
            $member->member_data->update($request->except('member_id'));
        } else {
            if($member->member_data->is_admin){
                $group->members->find($request->member_id)->member_data->update($request->except('member_id'));
            } else {
                return response()->json(['error' => 'User is not admin'], 400);
            }
        }

        return response()->json(null, 204);

    }

    public function deleteMember(Request $request, Group $group)
    {
        $user = Auth::guard('api')->user();
        $member = $group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        $validator = Validator::make($request->all(), [
            'member_id' => ['required','exists:users,id', new IsMember($group->id)],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        if($member == $group->members->find($request->member_id)){
            $group->members()->detach($member);
        } else {
            if($member->member_data->is_admin){
                $group->members()->detach(User::find($request->member_id));
            } else {
                return response()->json(['error' => 'User is not admin'], 400);
            }
        }

        if($group->members()->count() == 0){
            $group->delete();
        } else if($group->admins()->count() == 0){
            $group->members()->update(['is_admin' => true]);
        }
        return response()->json(null, 204);
    }
}
