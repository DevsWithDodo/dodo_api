<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Notifications\CustomNotification;

class AdminController extends Controller
{
    public function show()
    {
        return view('admin');
    }

    public function send_notification(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->notify(new CustomNotification($request->message));
        return response("Message sent to ".$user->username.'.');
    }
}
