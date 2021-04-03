<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Group;
use App\Mail\AdminAccess;

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

Route::get('/admin', function (Request $request) {
    if (!$request->hasValidSignature()) {
        $url = URL::temporarySignedRoute('admin', now()->addMinutes(30));
        Mail::to(config('app.admin_email'))->send(new AdminAccess($url));
        Mail::to(config('app.developer_email'))->send(new AdminAccess($url));
        return response("Secure link sent to the developer emails.");
    } else {
        return view('admin');
    }
})->name('admin');

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
