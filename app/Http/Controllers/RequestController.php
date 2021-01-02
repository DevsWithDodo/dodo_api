<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\Request as RequestResource;
use App\Notifications\FulfilledRequestNotification;
use App\Notifications\RequestNotification;
use App\Request as ShoppingRequest;
use App\Group;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        //$user = auth('api')->user(); //member
        $group = Group::findOrFail($request->group);

        return RequestResource::collection($group->requests);
    }

    public function store(Request $request)
    {
        $user = auth('api')->user();
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'name' => 'required|string|min:2|max:255',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        //the request is valid

        $shopping_request = ShoppingRequest::create([
            'name' => $request->name,
            "group_id" => $request->group,
            "requester_id" => $user->id,
        ]);

        //notify
        try {
            foreach ($shopping_request->group->members as $member)
                if ($member->id != $user->id)
                    $member->notify(new RequestNotification($shopping_request));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        return new RequestResource($shopping_request);
    }

    public function update(Request $request, ShoppingRequest $shopping_request)
    {
        if ($request->has('name')) {
            //TODO policy
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2|max:255',
            ]);
            if ($validator->fails()) abort(400, $validator->errors()->first());

            $shopping_request->update(['name' => $request->name]);

            return response()->json(null, 204);
        } else { //for backward compatibility
            return $this->delete($shopping_request);
        }
    }

    public function delete(ShoppingRequest $shopping_request)
    {
        $user = auth('api')->user();
        //TODO policy
        //notify
        try {
            $shopping_request->requester->notify(new FulfilledRequestNotification($shopping_request, $user));
        } catch (\Exception $e) {
            Log::error('FCM error', ['error' => $e]);
        }
        $shopping_request->delete();
        return response()->json(null, 204);
    }
}
