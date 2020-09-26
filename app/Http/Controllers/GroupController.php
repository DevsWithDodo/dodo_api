<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\CurrencyController;
use App\Rules\IsMember;
use App\Notifications\ChangedNicknameNotification;
use App\Notifications\ChangedGroupNameNotification;
use App\Notifications\PromotedToAdminNotification;
use App\Notifications\JoinedGroupNotification;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\User;
use App\Group;
use App\Invitation;

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
            'group_name' => 'required|string|min:1|max:20',
            'currency' => ['nullable','string','size:3', Rule::in(CurrencyController::currencyList())],
            'member_nickname' => 'nullable|string|min:1|max:15'
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
        
        $invitation = Invitation::create([
            'group_id' => $group->id,
            'token' => Str::random(20),
            'usable_once_only' => false
        ]);

        return response()->json(new GroupResource($group), 201);
    }

    public function update(Request $request, Group $group)
    {
        $user = Auth::guard('api')->user();
        Gate::authorize('edit-group', $group);
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:1|max:20',
            'currency' => ['nullable','string','size:3', Rule::in(CurrencyController::currencyList())],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $old_name = $group->name;
        $group->update($request->only('name', 'currency'));
        
        foreach($group->members as $member){
            if($member->id != $user->id) $member->notify(new ChangedGroupNameNotification($group, $user, $old_name, $group->name));
        }
                
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

    public function indexMember(Group $group) 
    {
        $user = Auth::guard('api')->user(); //member
        return new MemberResource($group->members->find($user));
    }

    public function addMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invitation_token' => 'required|string|exists:invitations,token',
            'nickname' => 'nullable|string|min:1|max:15',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = Auth::guard('api')->user();
        $invitation = Invitation::firstWhere('token', $request->invitation_token);
        $group = $invitation->group;

        if($group->members->count()==20){
            return response()->json(['error' => 'Group member limit reached'], 400);
        }
        if($group->members->contains($user)){
            return response()->json(['error' => 'The user is already a member in this group'], 400);
        } 
        $nickname = $request->nickname ?? $user->username;
        if($group->members->firstWhere('member_data.nickname', $nickname) != null){
            return response()->json(['error' => 'Please choose a new nickname.'], 400);
        }

        $group->members()->attach($user, [
            'nickname' => $nickname,
            'is_admin' => false
        ]);

        foreach($group->members as $member){
            if($member->id != $user->id) $member->notify(new JoinedGroupNotification($group, $nickname));
        }

        if($invitation->usable_once_only) {
            $invitation->delete();
        }
        
        return new GroupResource($group);
    }

    public function updateMember(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
            'nickname' => 'required|string|min:1|max:15',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $member_to_update = $group->members->find($request->member_id ?? $user->id);
        Gate::authorize('edit-member', [$member_to_update, $group]);
        
        if($group->members->firstWhere('member_data.nickname', $request->nickname) != null){
            return response()->json(['error' => 'Please choose a new nickname.'], 400);
        }
        $member_to_update->member_data->update(['nickname' => $request->nickname]);

        if($user->id != $member_to_update->id) $member_to_update->notify(new ChangedNicknameNotification($group, $user, $request->nickname));
        
        return response()->json(null, 204);
    }

    public function updateAdmin(Group $group, Request $request)
    {
        $user = Auth::guard('api')->user();
        Gate::authorize('edit-group', $group);
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'admin' => 'required|boolean',
        ]);
        $group->members->find($request->member_id)
            ->member_data->update(['is_admin' => $request->admin]);
        
        $member = User::find($request->member_id);
        if($request->admin && $member->id != $user->id){
            $member->notify(new PromotedToAdminNotification($group, $user));
        }
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