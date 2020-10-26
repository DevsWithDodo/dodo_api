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
use App\Notifications\PaymentNotification;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\Http\Resources\User as UserResource;

use App\Transactions\Payment;
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
        if($user->isGuest()) abort(400, "1");
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:1|max:20',
            'currency' => ['nullable','string','size:3', Rule::in(CurrencyController::currencyList())],
            'member_nickname' => 'nullable|string|min:1|max:15'
        ]);
        if($validator->fails()) abort(400, "0");

        $group = Group::create([
            'name' => $request->group_name,
            'currency' => $request->currency ?? $user->default_currency,
            'invitation' => Str::random(20)
        ]);

        $group->members()->attach($user, [
            'nickname' => $request->member_nickname ?? explode("#", $user->id)[0],
            'is_admin' => true //set to true on first member
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
        if($validator->fails()) abort(400, "0");

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
        if($validator->fails()) abort(400, "0");
        $user = Auth::guard('api')->user();
        $group = Group::firstWhere('invitation', $request->invitation_token);
        
        if($user->isGuest()) abort(400, "2");
        if($group->members->count()==20) abort(400, "3");
        if($group->members->contains($user)) abort(400, "4");
        $nickname = $request->nickname ?? $user->username;
        if($group->members->firstWhere('member_data.nickname', $nickname) != null) abort(400, "5");

        $group->members()->attach($user, [
            'nickname' => $nickname,
            'is_admin' => false
        ]);
        Cache::forget($group->id.'_balances');

        foreach($group->members as $member){
            if($member->id != $user->id) $member->notify(new JoinedGroupNotification($group, $nickname));
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
        if($validator->fails()) abort(400, "0");

        $member_to_update = $group->members->find($request->member_id ?? $user->id);
        Gate::authorize('edit-member', [$member_to_update, $group]);
        
        if($group->members->firstWhere('member_data.nickname', $request->nickname) != null) abort(400, "5");
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
        $member = User::find($request->member_id);
        if($member->isGuest()) abort(400, "6");
        $group->members->find($member)
            ->member_data->update(['is_admin' => $request->admin]);
                
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
        if($validator->fails()) abort(400, "0");
        $member_to_delete = $group->members->find($request->member_id ?? $user->id);
        Gate::authorize('edit-member', [$member_to_delete, $group]);

        $balance = $member_to_delete->member_data->balance;
        if($member_to_delete->id == $user->id)
        {
            if($balance < 0) abort(400, "7");
            if ($balance > 0) {
                $balance_divided = $balance / ($group->members->count()-1);
                foreach ($group->members->except([$user->id]) as $member) {
                    $payment = Payment::create([
                        'amount' => $balance_divided,
                        'group_id' => $group->id,
                        'taker_id' => $member->id,
                        'payer_id' => $user->id,
                        'note' => 'Legacy 💰💰💰'
                    ]);
                    $member->notify(new PaymentNotification($payment)); //TODO change
                }
            }
        } else {
            Payment::create([
                'amount' => $balance,
                'group_id' => $group->id,
                'taker_id' => $user->id,
                'payer_id' => $member_to_delete->id,
                'note' => 'Legacy 💰💰💰'
            ]);
        }
        
        Cache::forget($group->id.'_balances');
        $group->members()->detach($member_to_delete);

        if($group->members()->count() == 0){
            $group->delete();
        } else if($group->admins()->count() == 0){
            $group->members()->update(['is_admin' => true]);
        }
        return response()->json(null, 204);
    }

    /**
     * Guests
     */
    public function addGuest(Request $request, Group $group)
    {
        Gate::authorize('edit-group', $group);

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username'],
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())]
        ]);

        if($group->members->count()==20) abort(400, "3");
        if($group->members->firstWhere('member_data.nickname', $request->username) != null) abort(400, "5");
        $guest = User::create([
            'username' => $request->username,
            'password' => null,
            'password_reminder' => null,
            'default_currency' => $request->default_currency,
            'fcm_token' => null
        ]);
        $guest->generateToken(); // login 
        
        $group->members()->attach($guest, [
            'nickname' => $request->username,
            'is_admin' => false
        ]);
        Cache::forget($group->id.'_balances');

        foreach($group->members as $member){
            if($member->id != $guest->id) $member->notify(new JoinedGroupNotification($group, $request->username));
        }
        
        return response()->json(new UserResource($guest), 201);
    }

    public function mergeGuest(Request $request, Group $group)
    {
        Gate::authorize('edit-group', $group);
        
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
            'guest_id' => ['exists:users,id', new IsMember($group->id)],
        ]);

        $guest = User::find($request->guest_id);
        if(!$guest->isGuest()) abort(400, "8");

        $guest_id = $guest->id;
        $guest_balance = $group->members->find($guest)->member_data->balance;
        $member = User::find($request->member_id);
        $member_id = $member->id;
        
        $group->members()->detach($guest);
        $guest->delete();
        Cache::forget($group->id.'_balances');

        DB::table('buyers')->where('buyer_id', $guest_id)->update(['buyer_id' => $member_id]);
        DB::table('receivers')->where('receiver_id', $guest_id)->update(['receiver_id' => $member_id]);
        DB::table('payments')->where('payer_id', $guest_id)->update(['payer_id' => $member_id]);
        DB::table('payments')->where('taker_id', $guest_id)->update(['taker_id' => $member_id]);
        DB::table('requests')->where('requester_id', $guest_id)->update(['requester_id' => $member_id]);
        DB::table('requests')->where('fulfiller_id', $guest_id)->update(['fulfiller_id' => $member_id]);

        return response()->json(null, 204);
    }
}