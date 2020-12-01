<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\Request as RequestResource;
use App\Notifications\FulfilledRequestNotification;
use App\Notifications\RequestNotification;
use App\Request as ShoppingRequest;
use App\Group;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user(); //member
        $group = Group::findOrFail($request->group);

        return RequestResource::collection($group->requests);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'name' => 'required|string|min:2|max:50',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('name')) abort(400, "26");
            if ($errors->has('group')) abort(400, "23");
            abort(400, "0");
        }

        //the request is valid

        $shopping_request = ShoppingRequest::create([
            'name' => $request->name,
            "group_id" => $request->group,
            "requester_id" => $user->id,
        ]);

        //notify
        try{
            foreach ($shopping_request->group->members as $member)
                if ($member->id != $user->id)
                    $member->notify(new RequestNotification($shopping_request));
        } catch (Throwable $e) {
            report($e);
        }
        return new RequestResource($shopping_request);
    }

    public function fulfill(ShoppingRequest $shopping_request)
    {
        $user = Auth::guard('api')->user();
        $group = $shopping_request->group;
        if ($user->id == $shopping_request->requester->id) abort(400, "10");

        //notify
        try{
            $shopping_request->requester->notify(new FulfilledRequestNotification($shopping_request, $user));
        } catch (Throwable $e) {
            report($e);
        }
        $shopping_request->delete();
        return response()->json(200);
    }

    public function delete(ShoppingRequest $shopping_request)
    {
        $shopping_request->delete();
        return response()->json(null, 204);
    }
}
