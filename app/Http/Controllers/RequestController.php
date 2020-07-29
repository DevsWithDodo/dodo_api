<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Http\Resources\Request as RequestResource;
use App\Request as UserRequest;
use App\Group;
use App\User;

class RequestController extends Controller
{
    public function index(Group $group)
    {        
        $user = Auth::guard('api')->user();

        $active = $group->requests()
            ->where('fulfilled', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $fulfilled = $group->requests()
            ->where('fulfilled', true)
            ->where('fulfiller_id', $user->id)
            ->orWhere('requester_id', '=', $user->id)
            ->orderBy('fulfilled_at', 'desc')
            ->get();
        return new JsonResource(['active' => $active, 'fulfilled' => $fulfilled]);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $member = Group::find($request->group_id)->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }

        $user_request = UserRequest::create([
            'name' => $request->name,
            "group_id" => $request->group_id,
            "requester_id" => $user->id,
        ]);

        return new RequestResource($user_request);
    }

    public function fulfill(UserRequest $user_request)
    {
        if($user_request->fulfilled){
            return response()->json(['error' => 'Request already fulfilled'], 400);
        }
        $user = Auth::guard('api')->user();
        $member = $user_request->group->members->find($user);
        if($member == null){
            abort(400, 'User is not a member of this group.');
        }
        if($user == $user_request->requester){
            return response()->json(['error' => 'Cannot be fulfilled by requester.'], 400);
        }

        $user_request->update([
            'fulfiller_id' => $user->id,
            'fulfilled' => true,
            'fulfilled_at' => Carbon::now()
        ]);
        return new RequestResource($user_request);
    }

    public function delete(UserRequest $user_request)
    {
        $user = Auth::guard('api')->user();
        if($user_request->requester == $user) {
            $user_request->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'Request can only be deleted by requester'], 400);
        }
    }
}
