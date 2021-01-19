<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\Request as RequestResource;
use App\Notifications\Requests\FulfilledRequestNotification;
use App\Notifications\Requests\RequestNotification;
use App\Notifications\Requests\ShoppingNotification;
use App\Request as ShoppingRequest;
use App\Group;
use App\Transactions\Reactions\RequestReaction;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $group = Group::findOrFail($request->group);
        $this->authorize('member', $group);
        return RequestResource::collection($group->requests()->with('reactions')->get());
    }

    public function store(Request $request)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'name' => 'required|string|min:2|max:255',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $group = Group::find($request->group);
        $this->authorize('member', $group);

        $shopping_request = ShoppingRequest::create([
            'name' => $request->name,
            "group_id" => $group->id,
            "requester_id" => $user->id,
        ]);

        try {
            foreach ($group->members->except($user->id) as $member)
                $member->notify(new RequestNotification($shopping_request));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        return new RequestResource($shopping_request);
    }

    public function update(Request $request, ShoppingRequest $shopping_request)
    {
        $this->authorize('update', $shopping_request);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $shopping_request->update(['name' => $request->name]);

        return response()->json(null, 204);
    }

    public function delete(ShoppingRequest $shopping_request)
    {
        $this->authorize('delete', $shopping_request);
        $user = auth('api')->user();

        try {
            if ($shopping_request->requester_id != $user->id)
                $shopping_request->requester->notify(new FulfilledRequestNotification($shopping_request, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }

        $shopping_request->delete();
        return response()->json(null, 204);
    }

    public function reaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:requests,id',
            'reaction' => 'required|string|min:1|max:1'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = auth('api')->user();
        $reaction = RequestReaction::where('user_id', $user->id)
            ->where('request_id', $request->request_id)
            ->first();

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else RequestReaction::create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'request_id' => $request->request_id,
            'group_id' => ShoppingRequest::find($request->request_id)->group_id
        ]);

        return response()->json(null, 204);
    }

    /**
     * Send a notification to the group members that the user is currently shopping.
     */
    public function sendShoppingNotification(Request $request, Group $group)
    {
        $user = auth('api')->user();
        $this->authorize('member', $group);
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
