<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* For testing */
Route::get('/transactions', function(){ return App\Http\Resources\Transaction::collection(App\Transactions\Purchase::all());}); 
Route::get('/users', function(){ return App\User::all(); });
Route::get('/groups_all', function() { return App\Http\Resources\Group::collection(App\Group::all()); });

/* Auth */
Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->middleware('auth:api');

Route::middleware(['auth:api'])->group(function () {
    /* User related */
    Route::get('/users/{user}', 'UserController@show');
    Route::get('/balance', 'UserController@balance');
    
    /* Groups */
    Route::get('/groups', 'GroupController@index');
    Route::get('/groups/{group}', 'GroupController@show');
    Route::post('/groups', 'GroupController@store');
    Route::put('/groups/{group}', 'GroupController@update');
    Route::delete('/groups/{group}', 'GroupController@delete');
    
    /* Members */
    Route::post('/groups/{group}/members', 'GroupController@addMember');
    Route::put('/groups/{group}/members', 'GroupController@updateMember');
    Route::delete('/groups/{group}/members', 'GroupController@deleteMember');

    /* Transactions */
    Route::get('/transactions/groups/{group}', 'TransactionController@index');
    Route::post('/transactions', 'TransactionController@store');
    Route::put('/transactions/{purchase}', 'TransactionController@update');
    Route::delete('/transactions/{purchase}', 'TransactionController@delete');

    /* Payments */
    Route::get('/payments/groups/{group}', 'PaymentController@index');
    Route::post('/payments', 'PaymentController@store');
    Route::put('/payments/{payment}', 'PaymentController@update');
    Route::delete('/payments/{payment}', 'PaymentController@delete');

    /* Requests*/
    Route::get('/requests/groups/{group}', 'RequestController@index');
    Route::post('/requests', 'RequestController@store');
    Route::put('/requests/{user_request}', 'RequestController@fulfill');
    Route::delete('/requests/{user_request}', 'RequestController@delete');
});
