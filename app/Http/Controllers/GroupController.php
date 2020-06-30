<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\GroupCollection;
use App\Group;

class GroupController extends Controller
{
    //List all group
    public function index()
    {
        return new GroupCollection(Group::all());
    }

    //Return a group with its members 
    public function show(Request $request, $id)
    {
        return new GroupResource(Group::find($id));
    }

    public function createGroup(Request $request)
    {
        //TODO
    }
}
