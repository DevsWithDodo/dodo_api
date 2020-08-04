<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\CurrencyController;
use App\Rules\IsMember;

use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\User;
use App\Group;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::guard('api')->user();
        return GroupResource::collection($user->groups);
    }
    
    /**
     * Show a group's details.
     * Updates the User's $last_active_group attribute with this group.
     */
    public function show(Group $group)
    {
        $user = Auth::guard('api')->user(); //member
        $user->update(['last_active_group' => $group->id]);
        return new GroupResource($group);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:3|max:20',
            'currency' => ['nullable','string','size:3', Rule::in(CurrencyController::currencyList())],
            'member_nickname' => 'nullable|string|min:3|max:15'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group = Group::create([
            'name' => $request->group_name,
            'currency' => $request->currency ?? $user->default_currency
        ]);

        $group->members()->attach($user, [
            'nickname' => $request->member_nickname ?? explode("#", $user->id)[0],
            'is_admin' => true //set to true on first member
        ]);

        return response()->json(new GroupResource($group), 201);
    }

    public function update(Request $request, Group $group)
    {
        Gate::authorize('edit-group', $group);
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:3|max:20',
            'currency' => ['nullable','string','size:3', Rule::in(CurrencyController::currencyList())],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $group->update($request->only('name', 'currency'));
        
        return response()->json(new GroupResource($group), 200);
    }

    public function delete(Group $group)
    {
        Gate::authorize('edit-group', $group);
        
        $group->delete();
    
        return response()->json(null, 204);
    }

    /**
     * Member related functions
     */

    public function addMember(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        if($group->members->count()==20){
            return response()->json(['error' => 'Group member limit reached'], 400);
        }
        $validator = Validator::make($request->all(), [
            'nickname' => 'nullable|string|min:3|max:15',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        if($group->members->find($user) == null){
            $nickname = $request->nickname ?? explode("#", $user->id)[0];
            if($group->members->firstWhere('member_data.nickname', $nickname) != null){
                return response()->json(['error' => 'Please choose a new nickname.'], 400);
            }
            $group->members()->attach($user, [
                'nickname' => $nickname,
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
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
            'nickname' => 'required|string|min:3|max:15',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $member_to_update = $group->members->find($request->member_id ?? $user->id);
        Gate::authorize('edit-member', [$member_to_update, $group]);
        
        if($group->members->firstWhere('nickname', $request->nickname) != null){
            return response()->json(['error' => 'Please choose a new nickname.'], 400);
        }
        $member_to_update->member_data->update(['nickname' => $request->nickname]);
        
        return response()->json(null, 204);
    }

    public function updateAdmin(Group $group, Request $request)
    {
        Gate::authorize('edit-group', $group);
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'admin' => 'required|boolean',
        ]);
        
        $group->members->find($request->member_id)
            ->member_data->update(['is_admin' => $request->admin]);
        
        if($group->admins()->count() == 0){
            $group->members()->update(['is_admin' => true]);
        }

        return response()->json(null, 204);
    }

    public function deleteMember(Request $request, Group $group)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $member_to_delete = $group->members->find($request->member_id ?? $user->id);
        Gate::authorize('edit-member', [$member_to_delete, $group]);
        
        $group->members()->detach($member_to_delete);

        if($group->members()->count() == 0){
            $group->delete();
        } else if($group->admins()->count() == 0){
            $group->members()->update(['is_admin' => true]);
        }
        return response()->json(null, 204);
    }
}