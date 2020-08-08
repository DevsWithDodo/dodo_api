<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

use App\Http\Resources\Invitation as InvitationResource;
use App\Invitation;
use App\Group;
use App\User;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group' => 'required|exists:groups,id',
            'usable_once_only' => 'required|boolean'
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $group = Group::find($request->group);
        
        if(!$group->anyone_can_invite){
            Gate::authorize('edit-group', $group);
        }
        
        do {
            $token = Str::random(20);
        } while (DB::table('invitations')->first('token', $token) == null);

        $invitation = Invitation::create([
            'group_id' => $group->id,
            'token' => $token,
            'usable_once_only' => $request->usable_once_only
        ]);
        return new InvitationResource($invitation);
    } 

    public function delete(Invitation $invitation)
    {
        if(!$invitation->group->anyone_can_invite){
            Gate::authorize('edit-group', $invitation->group);
        }
        
        $invitation->delete();

        return response()->json(null, 204);
    } 
}
