<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Http\Resources\Request as RequestResource;
use App\Notifications\FulfilledRequestNotification;
use App\Notifications\RequestNotification;
use App\Request as ShoppingRequest;
use App\Group;
use App\User;

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
        if($validator->fails()){
            return response()->json(['error' => 0], 400);
        }
        $shopping_request = ShoppingRequest::create([
            'name' => $request->name,
            "group_id" => $request->group,
            "requester_id" => $user->id,
        ]);
        foreach ($shopping_request->group->members as $member) {
            if($member->id != $user->id){
                $member->notify(new RequestNotification($shopping_request));
            }
        }
        return new RequestResource($shopping_request);
    }

    public function fulfill(ShoppingRequest $shopping_request)
    {
        $user = Auth::guard('api')->user();
        $group = $shopping_request->group;
        if($user->id == $shopping_request->requester->id){
            return response()->json(['error' => 10], 400);
        }
        $shopping_request->requester->notify(new FulfilledRequestNotification($shopping_request));
        
        $shopping_request->delete();
        return response()->json(200);
    }

    public function delete(ShoppingRequest $shopping_request)
    {
        $shopping_request->delete();
        return response()->json(null, 204);
    }
}
