<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\CurrencyController;
use App\Rules\IsMember;
use App\Rules\UniqueNickname;
use App\Notifications\ChangedNicknameNotification;
use App\Notifications\PromotedToAdminNotification;
use App\Notifications\JoinedGroupNotification;
use App\Notifications\PaymentNotification;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Member as MemberResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Log;

use App\Transactions\Payment;
use App\User;
use App\Group;

class MemberController extends Controller
{
    public function index(Group $group)
    {
        $user = auth('api')->user(); //member
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
        Cache::forget($group->id . '_balances');

        //notify
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

        $member_to_update = $group->members->find($request->member_id ?? $user->id);

        $this->authorize('edit_member', [$group, $member_to_update]);

        //the request is valid

        $member_to_update->member_data->update(['nickname' => $request->nickname]);

        //notify
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
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'admin' => 'required|boolean',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $member = User::find($request->member_id);
        $this->authorize('edit_admin', [$group, $member]);

        //the request is valid

        $group->members->find($member)
            ->member_data->update(['is_admin' => $request->admin]);

        //notify
        try {
            if ($request->admin && $member->id != $user->id)
                $member->notify(new PromotedToAdminNotification($group, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        //make everyone an admin if there is no admin left
        if ($group->admins()->count() == 0)
            $group->members()->update(['is_admin' => true]);

        return response()->json(null, 204);
    }

    public function delete(Request $request, Group $group)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'member_id' => ['exists:users,id', new IsMember($group->id)],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $member_to_delete = $group->members->find($request->member_id ?? $user->id);
        $this->authorize('edit_member', [$group, $member_to_delete]);

        //the request is valid

        $balance = $group->balances()[$member_to_delete->id];
        if ($member_to_delete->id == $user->id) {
            if ($balance < 0) abort(400, "7");
            if ($balance > 0) {
                $balance_divided = $balance / ($group->members->count() - 1);
                foreach ($group->members->except([$user->id]) as $member) {
                    $payment = Payment::create([
                        'amount' => (-1) * $balance_divided,
                        'group_id' => $group->id,
                        'taker_id' => $member->id,
                        'payer_id' => $user->id,
                        'note' => '$$legacy_money$$'
                    ]);
                    try {
                        $member->notify(new PaymentNotification($payment)); //TODO change
                    } catch (\Exception $e) {
                        Log::error('FCM error', ['error' => $e]);
                    }
                }
            }
        } else {
            Payment::create([
                'amount' => (-1) * $balance,
                'group_id' => $group->id,
                'taker_id' => $user->id,
                'payer_id' => $member_to_delete->id,
                'note' => '$$legacy_money$$'
            ]);
        }

        $group->requests()->where('requester_id', $member_to_delete->id)->delete();

        Cache::forget($group->id . '_balances');

        $group->members()->detach($member_to_delete);

        //TODO notify

        if ($group->members()->count() == 0) {
            $group->delete();
        } else if ($group->admins()->count() == 0) {
            $group->members()->update(['is_admin' => true]);
        }

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
        $this->authorize('edit', $group);
        $this->authorize('add_guest', $group);
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'regex:/^[a-z0-9#.]{3,15}$/', 'unique:users,username', new UniqueNickname($group->id)],
            'language' => 'required|in:en,hu,it,de',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        $guest = User::create([
            'username' => $request->username,
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
        Cache::forget($group->id . '_balances');

        //notify
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
     * Merge a guest's data to a member's data and delete guest.
     **/
    public function mergeGuest(Request $request, Group $group)
    {
        $this->authorize('edit', $group);
        $validator = Validator::make($request->all(), [
            'member_id' => ['required', 'exists:users,id', new IsMember($group->id)],
            'guest_id' => ['required', 'exists:users,id', new IsMember($group->id)],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $guest = User::find($request->guest_id);
        $member = User::find($request->member_id);

        if (!$guest->isGuest()) abort(400, '$$available_for_guests_only$$');

        //the request is valid

        //change the guest's id to the member's id in the tables
        DB::table('purchases')->where('buyer_id', $guest->id)->update(['buyer_id' => $member->id]);
        DB::table('purchase_receivers')->where('receiver_id', $guest->id)->update(['receiver_id' => $member->id]);
        DB::table('payments')->where('payer_id', $guest->id)->update(['payer_id' => $member->id]);
        DB::table('payments')->where('taker_id', $guest->id)->update(['taker_id' => $member->id]);
        DB::table('requests')->where('requester_id', $guest->id)->update(['requester_id' => $member->id]);

        $group->members()->detach($guest);
        $guest->delete();

        Cache::forget($group->id . '_balances');

        return response()->json(null, 204);
    }
}
