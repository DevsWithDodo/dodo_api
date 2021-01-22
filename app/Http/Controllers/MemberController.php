<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

use App\Rules\IsMember;
use App\Rules\UniqueNickname;
use App\Notifications\Members\ChangedNicknameNotification;
use App\Notifications\Members\PromotedToAdminNotification;
use App\Notifications\Groups\JoinedGroupNotification;
use App\Notifications\Transactions\PaymentNotification;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Member as MemberResource;
use Illuminate\Support\Facades\Log;

use App\Transactions\Payment;
use App\User;
use App\Group;

class MemberController extends Controller
{
    public function show(Group $group)
    {
        $user = auth('api')->user();
        $this->authorize('view', $group);
        return new MemberResource($group->members->find($user));
    }

    public function store(Request $request)
    {
        $user = auth('api')->user();
        $group = Group::firstWhere('invitation', $request->invitation_token);
        $validator = Validator::make($request->all(), [
            'invitation_token' => 'required|string|exists:groups,invitation',
            'nickname' => 'required|string|min:1|max:15', new UniqueNickname($group->id),
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $this->authorize('join', $group);

        $group->members()->attach($user, [
            'nickname' => $request->nickname,
            'is_admin' => false
        ]);

        try {
            foreach ($group->members as $member)
                if ($member->id != $user->id)
                    $member->notify(new JoinedGroupNotification($group, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        return new GroupResource($group);
    }

    public function update(Group $group, Request $request)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
            'nickname' => 'required|string|min:1|max:15', new UniqueNickname($group->id),
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $member_to_update = $group->member($request->member_id ?? $user->id);

        $this->authorize('edit_member', [$group, $member_to_update]);

        $member_to_update->member_data->update(['nickname' => $request->nickname]);

        try {
            if ($user->id != $member_to_update->id)
                $member_to_update->notify(new ChangedNicknameNotification($group, $user, $request->nickname));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        return response()->json(null, 204);
    }

    public function updateAdmin(Group $group, Request $request)
    {
        $user = auth('api')->user();
        $this->authorize('edit_admins', $group);
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'admin' => 'required|boolean',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $member = User::find($request->member_id);
        $group->member($member->id)
            ->member_data->update(['is_admin' => $request->admin]);

        try {
            if ($request->admin && $member->id != $user->id)
                $member->notify(new PromotedToAdminNotification($group, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        //make everyone an admin if there is no admin left
        if ($group->admins()->count() == 0)
            foreach ($group->members as $member)
                $member->member_data->update(['is_admin' => true]);

        return response()->json(null, 204);
    }

    public function delete(Request $request, Group $group)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $member_to_delete = $group->member($request->member_id ?? $user->id);
        $this->authorize('edit_member', [$group, $member_to_delete]);

        $balance = $group->member($member_to_delete->id)->member_data->balance;
        if ($member_to_delete->id == $user->id) { //leaving
            if ($balance < 0) abort(400, __('errors.balance_negative'));
            if ($balance > 0) {
                $balance_divided = bcdiv($balance, ($group->members->count() - 1));
                $remainder = bcsub($balance, bcmul($balance_divided, $group->members->count() - 1));
                foreach ($group->members->except([$user->id]) as $member) {
                    $payment = Payment::create([
                        'amount' => (-1) * bcadd($balance_divided, $remainder),
                        'group_id' => $group->id,
                        'taker_id' => $member->id,
                        'payer_id' => $user->id,
                        'note' => '$$legacy_money$$'
                    ]);
                    $remainder = 0;
                    try {
                        $member->notify(new PaymentNotification($payment)); //TODO change
                    } catch (\Exception $e) {
                        Log::error('FCM error', ['error' => $e]);
                    }
                }
            }
        } else { //kicking
            Payment::create([
                'amount' => (-1) * $balance,
                'group_id' => $group->id,
                'taker_id' => $user->id,
                'payer_id' => $member_to_delete->id,
                'note' => '$$legacy_money$$'
            ]);
        }

        $group->requests()->where('requester_id', $member_to_delete->id)->delete();
        $group->members()->detach($member_to_delete->id);

        if ($member_to_delete->isGuest()) $member_to_delete->delete();

        if ($group->members()->count() == 0)
            $group->delete();
        else if ($group->admins()->count() == 0)
            foreach ($group->members as $member)
                $member->member_data->update(['is_admin' => true]);

        return response()->json(null, 204);
    }

    /**
     * Guests
     */

    public function hasGuests(Group $group)
    {
        $this->authorize('edit', $group);
        return response()->json(['data' => ($group->guests->count() > 0 ? 1 : 0)]);
    }

    public function addGuest(Request $request, Group $group)
    {
        $this->authorize('add_guest', $group);
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:1|max:15', new UniqueNickname($group->id),
            'language' => 'required|in:en,hu,it,de',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $guest = User::create([
            'username' => null,
            'password' => null,
            'password_reminder' => null,
            'default_currency' => $group->currency,
            'fcm_token' => null,
            'language' => $request->language
        ]);
        $guest->generateToken(); // login

        $group->members()->attach($guest, [
            'nickname' => $request->username,
            'is_admin' => false
        ]);

        try {
            foreach ($group->members as $member)
                if ($member->id != $guest->id)
                    $member->notify(new JoinedGroupNotification($group, $guest));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        return response()->json(new UserResource($guest), 201);
    }

    /**
     * Merge a guest's data to a member's data and delete the guest.
     **/
    public function mergeGuest(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'guest_id' => ['required', 'exists:users,id', new IsMember($group->id)],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $guest = User::findOrFail($request->guest_id);
        $member = User::findOrFail($request->member_id);
        $this->authorize('merge_guest', [$group, $guest]);

        //the request is valid

        $balance = $group->member($guest->id)->member_data->balance;

        $guest->mergeDataInto($member->id);
        $group->members()->detach($guest);
        $guest->delete();

        $group->addToMemberBalance($member->id, $balance);

        return response()->json(null, 204);
    }
}
