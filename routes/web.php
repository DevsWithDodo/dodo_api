<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Notifications\CustomNotification;

use App\Group;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy-policy', function () {
    return view('privacy_policy');
});

Route::get('/join/{token}', function ($token) {
    return view('join', ['group' => Group::firstWhere('invitation', $token)]);
});

Route::get('/admin', function () {
    return view('admin');
})->middleware('passwordprotect:1');

Route::post('/admin/recalculate', function (Request $request) {
    Group::findOrFail($request->group)->recalculateBalances();
    return redirect()->back();
})->middleware('passwordprotect:1');

Route::post('admin/send_notification', function (Request $request) {
    if ($request->everyone) {
        foreach (\App\User::all() as $user)
            $user->notify(new CustomNotification($request->message));
        return response("Message sent to everyone.");
    } else {
        $user = \App\User::findOrFail($request->id);
        $user->notify(new CustomNotification($request->message));
        return response("Message sent to " . $user->username . '.');
    }
})->middleware('passwordprotect:1');

Route::get('/landscape_preview', function () {
    $path = public_path() . '/lender_preview.png';

    if (!File::exists($path)) {
        return response()->json(['message' => 'Image not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Auth::routes();
