<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Http\Controllers\CurrencyController;
use App\Http\Resources\User as UserResource;

use App\User;
use App\Group;

class UserController extends Controller
{
    public function show()
    {
        $user = Auth::guard('api')->user();
        return new UserResource($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
            'default_currency' => ['required', 'string', 'size:3', Rule::in(CurrencyController::currencyList())],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_reminder' => ['string']
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        
        if(User::find($id) == null){
            $user = User::create([
                'id' => $id,
                'password' => Hash::make($request->password),
                'password_reminder' => $request->password_reminder ?? null,
                'default_currency' => $request->default_currency
            ]);
            $user->generateToken(); // login 

            return response()->json(['data' => $user->toArray()], 201);
        } else {
            return response()->json(['error' => "Id is already taken."], 400);
        }
    }

    public function changePassword(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:4|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        } 
        try {
            if ((Hash::check($request->old_password, $user->password)) == false) {
                return response()->json(['error' => 'Check your old password'],400);
            } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                return response()->json(['error' => 'Please enter a password which is not similar then current password.'],400);
            } else {
                $user->update(['password' => Hash::make($request->new_password)]);
                return response()->json(null, 204);
            }
        } catch (\Exception $ex) {
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            } else {
                $msg = $ex->getMessage();
            }
            return response()->json(["error" => $msg], 400);
        }
    }

    public function isValidId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        return response()->json(User::find($id) == null);
    }

    public function passwordReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_name' => ['required', 'alpha_num', 'min:4', 'max:20'],
            'id_token' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 400);
        }
        $id = strtolower($request->id_name)."#".sprintf("%04d",$request->id_token);
        $user = User::find($id);
        if($user == null){
            return response()->json(['error' => 'User does not exist.'], 400);
        }
        return response()->json($user->password_reminder ?? "");
    }
}
