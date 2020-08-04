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

Route::get('/join', function (Request $request) {
    if ($request->has('token')){
        $invitation = Invitation::firstWhere('token', $request->token);
        return view('join', ['invitation' => $invitation]);
    }
    return view('welcome');
});

Auth::routes();

