<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\RequestResource as RequestResource;
use App\Notifications\Requests\FulfilledRequestNotification;
use App\Notifications\Requests\RequestNotification;
use App\Notifications\Requests\ShoppingNotification;
use App\Request as ShoppingRequest;
use App\Group;

class RequestController extends Controller {
    public function index(Request $request) {
        $group = Group::findOrFail($request->group);
        $this->authorize('member', $group);
        return RequestResource::collection($group->requests()->with('reactions')->get());
    }

    public function store(Request $request) {
        $user = auth('api')->user();
        $group = Group::with('members')->findOrFail($request->group);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());
        $this->authorize('member', $group);

        $shopping_request = ShoppingRequest::create([
            'name' => $request->name,
            "group_id" => $group->id,
            "requester_id" => $user->id,
        ]);

        foreach ($group->members->except($user->id) as $member)
            $member->sendNotification((new RequestNotification($shopping_request)));
        return RequestResource::make($shopping_request);
    }

    public function update(Request $request, ShoppingRequest $shopping_request) {
        $this->authorize('update', $shopping_request);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $shopping_request->update(['name' => $request->name]);

        return RequestResource::make($shopping_request);
    }

    public function restore($shopping_request) {
        $shopping_request = ShoppingRequest::withTrashed()->findOrFail($shopping_request);
        $this->authorize('delete', $shopping_request);

        $shopping_request->restore();

        return RequestResource::make($shopping_request);
    }
    public function delete(ShoppingRequest $shopping_request) {
        $this->authorize('delete', $shopping_request);
        $user = auth('api')->user();

        if ($shopping_request->requester_id != $user->id)
            $shopping_request->requester->sendNotification((new FulfilledRequestNotification($shopping_request, $user)));

        $shopping_request->delete();
        return response()->json(null, 204);
    }

    public function reaction(Request $request) {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:requests,id',
            'reaction' => 'required|string|min:1|max:1'
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $user = auth('api')->user();
        $shoppingRequest = ShoppingRequest::find($request->request_id);
        $this->authorize('member', $shoppingRequest->group);
        $reaction = $shoppingRequest
            ->reactions()
            ->where('user_id', $user->id)
            ->first();

        //Create, update, or delete reaction
        if ($reaction) {
            if ($reaction->reaction != $request->reaction)
                $reaction->update(['reaction' => $request->reaction]);
            else $reaction->delete();
        } else $shoppingRequest->reactions()->create([
            'reaction' => $request->reaction,
            'user_id' => $user->id,
            'group_id' => $shoppingRequest->group_id
        ]);
        return response()->json(null, 204);
    }

    /**
     * Send a notification to the group members that the user is currently shopping.
     */
    public function sendShoppingNotification(Request $request, Group $group) {
        $user = auth('api')->user();
        $this->authorize('member', $group);
        $validator = Validator::make($request->all(), [
            'store' => ['required', 'string', 'max:20'],
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        foreach ($group->members->except($user->id) as $member)
            $member->sendNotification((new ShoppingNotification($group, $user, $request->store)));
        return response()->json(null, 204);
    }
}
