<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMember;

use App\ShoppingCart;
use App\Group;
use App\User;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {        
        $group = Group::findOrFail($request->group_id);
        return response()->json($group->shopping_carts->where('fulfilled', false)->get());
    }

    public function show(ShoppingCart $shopping_cart)
    {
        return response()->json($shopping_cart);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|integer|min:0',
            'requester_id' => ['required','exists:users,id', new IsMember($request->group_id)],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }

        $shopping_cart = ShoppingCart::create($request->all());

        return response()->json($shopping_cart);
    }

    public function fulfill(Request $request, ShoppingCart $shopping_cart)
    {
        $validator = Validator::make($request->all(), [
            'fulfiller_id' => ['required','exists:users,id', new IsMember($request->group_id)]
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $shopping_cart->update([
            'fulfiller_id' => $request->fulfiller_id,
            'fulfilled' => true
        ]);

        return response()->json($shopping_cart);
    }

    public function delete(ShoppingCart $shopping_cart)
    {
        $shopping_cart->delete();

        return response()->json(null, 204);
    }
}
