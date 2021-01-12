<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Http\Controllers\CurrencyController;
use App\Notifications\ChangedGroupNameNotification;
use App\Notifications\ShoppingNotification;
use App\Http\Resources\Group as GroupResource;
use Illuminate\Support\Facades\Log;

use App\Group;

class GroupController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        return GroupResource::collection($user->groups);
    }

    public function show(Group $group)
    {
        $user = auth('api')->user(); //member
        $user->update(['last_active_group' => $group->id]);
        return new GroupResource($group);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->isGuest()) abort(400, '$$unavailable_for_guests$$');

        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|min:1|max:20',
            'currency' => ['nullable', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'member_nickname' => ['nullable', 'string', 'min:1', 'max:15'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

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
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:1|max:20',
            'currency' => ['nullable', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $this->authorize('edit', $group);
        //the request is valid

        $old_name = $group->name;
        $group->update($request->only('name', 'currency'));

        //notify
        try {
            if ($old_name != $group->name) {
                foreach ($group->members->except($user->id) as $member)
                    $member->notify(new ChangedGroupNameNotification($group, $user, $old_name, $group->name));
            }
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

    /* I'm shopping notification */
    public function sendShoppingNotification(Request $request, Group $group)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'store' => ['required', 'string', 'max:20'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        try {
            foreach ($group->members->except($user->id) as $member)
                $member->notify(new ShoppingNotification($group, $user, $request->store));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        return response()->json(null, 204);
    }
}
