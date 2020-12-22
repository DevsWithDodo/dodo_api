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
        if($request->everyone)
        {
            foreach (User::all() as $user)
                $user->notify(new CustomNotification($request->message, $request->screen));
            return response("Message sent to everyone.");
        } else {
            $user = User::findOrFail($request->id);
            $user->notify(new CustomNotification($request->message, $request->screen));
            return response("Message sent to ".$user->username.'.');
        }

    }
}
