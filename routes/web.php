<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Invitation;

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

Route::get('/join/{token}', function ($token) {
    $invitation = Invitation::firstWhere('token', $token);
    return view('join', ['invitation' => $invitation]);
});

Route::get('/link_prw', function(){
    $path = public_path() . '/lender_preview.png';

    if(!File::exists($path)) {
        return response()->json(['message' => 'Image not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Auth::routes();

