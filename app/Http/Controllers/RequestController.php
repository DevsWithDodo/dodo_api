<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Http\Resources\Request as RequestResource;
use App\Notifications\RequestNotification;
use App\Request as ShoppingRequest;
use App\Group;
use App\User;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user(); //member

        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group = Group::find($request->group);

        $active = RequestResource::collection(
            $group->requests()
                ->where('fulfilled', false)
                ->orderBy('created_at', 'desc')
                ->get()
            );

        $fulfilled = [];
        foreach ($group->requests()->where('fulfilled', true)->orderBy('created_at', 'desc')->get() as $request) {
            if($request->fulfiller_id == $user->id || $request->requester_id == $user->id){
                $fulfilled[] = new RequestResource($request);
            }
        }
        return new JsonResource(['active' => $active, 'fulfilled' => $fulfilled]);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'name' => 'required|string|min:2|max:20',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
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
        if($shopping_request->fulfilled){
            return response()->json(['error' => 'Request already fulfilled'], 400);
        }
        if($user == $shopping_request->requester){
            return response()->json(['error' => 'Cannot be fulfilled by requester.'], 400);
        }
        if(!$group->members->contains($user)){
            return response()->json(['error' => 'User is not a member of this group'], 400);
        }

        $shopping_request->update([
            'fulfiller_id' => $user->id,
            'fulfilled' => true,
            'fulfilled_at' => Carbon::now()
        ]);
        return new RequestResource($shopping_request);
    }

    public function delete(ShoppingRequest $shopping_request)
    {
        $shopping_request->delete();
        return response()->json(null, 204);
    }
}
