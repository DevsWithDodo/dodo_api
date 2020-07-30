<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
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
}
