<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Group as GroupResource;
use App\Group;

class GroupController extends Controller
{
    //List all group
    public function index()
    {
        return GroupResource::collection(Group::all());
    }

    //Return a group with its members 
    public function show(Group $group)
    {
        return new GroupResource($group);
    }

    public function createGroup(Request $request)
    {
        //TODO
    }
}
