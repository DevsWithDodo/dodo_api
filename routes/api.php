<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Group as GroupResource;
use App\User;
use App\Group;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/users', function () {
    return UserResource::collection(User::all());
});

Route::get('/groups', 'GroupController@index');
Route::get('/groups/{id}', 'GroupController@show');

Route::get('/groups/{group}/transactions', 'TransactionController@index');
Route::get('/groups/{group}/transactions/{purchase}', 'TransactionController@show');
Route::post('/groups/{group}/transactions', 'TransactionController@store');
Route::put('/groups/{group}/transactions/{purchase}', 'TransactionController@update');
Route::delete('/groups/{group}/transactions/{purchase}', 'TransactionController@delete');