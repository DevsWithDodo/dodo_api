<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\CurrencyController;
use App\Notifications\Groups\ChangedGroupNameNotification;
use App\Notifications\Groups\GroupBoostedNotification;
use App\Http\Resources\Group as GroupResource;
use App\Group;


class GroupController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        return response()->json(['data' => $user->groups->map(function ($i) {return ['group_id' => $i->id, 'group_name' => $i->name, 'currency' => $i->currency];})]);
    }

    public function show(Request $request, Group $group)
    {
        $this->authorize('view', $group);
        $user = $request->user();
        $user->update(['last_active_group' => $group->id]);
        return new GroupResource($group);
    }

    public function store(Request $request)
    {
        $this->authorize('is_not_guest');
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:1|max:20',
            'currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'member_nickname' => ['required', 'string', 'min:1', 'max:15'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $group = Group::create([
            'name' => $request->group_name,
            'currency' => $request->currency,
            'invitation' => Str::random(20)
        ]);

        $group->members()->attach(auth('api')->user()->id, [
            'nickname' => $request->member_nickname,
            'is_admin' => true //set to true on first member
        ]);

        return response()->json(new GroupResource($group), 201);
    }

    public function isBoosted(Request $request, Group $group)
    {
        $this->authorize('view', $group);
        $user = $request->user();
        return response()->json(['data' => [
            'is_boosted' => $group->boosted ? 1 : 0,
            'available_boosts' => $user->available_boosts,
            'trial' => $user->trial ? 1 : 0,
            'created_at' => $group->created_at
        ]]);
    }
    public function boost(Request $request, Group $group)
    {
        $this->authorize('boost', $group);
        $user = $request->user();
        $user->decrement('available_boosts');
        $group->update(['boosted' => true]);

        try {
            foreach ($group->members->except($user->id) as $member)
                $member->notify(new GroupBoostedNotification($group, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        return response()->json(null, 204);
    }

    public function update(Request $request, Group $group)
    {
        $this->authorize('edit', $group);
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:1|max:20',
            'currency' => ['nullable', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = auth('api')->user();
        $old_name = $group->name;
        $group->update($request->only('name', 'currency'));

        try {
            if ($old_name != $group->name)
                foreach ($group->members->except($user->id) as $member)
                    $member->notify(new ChangedGroupNameNotification($group, $user, $old_name, $group->name));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        return response()->json(new GroupResource($group), 200);
    }

    public function delete(Group $group)
    {
        $this->authorize('edit', $group);
        $group->delete();
        return response()->json(null, 204);
    }
}
