<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* For testing */
Route::get('/users', function(){ return App\User::all(); });
Route::get('/groups_all', function() { return App\Http\Resources\Group::collection(App\Group::all()); });

/* Auth */
Route::post('register', 'UserController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->middleware('auth:api');
Route::post('change_password', 'UserController@changePassword')->middleware('auth:api');
Route::post('change_id', 'UserController@changeId')->middleware('auth:api');
Route::get('is_valid_id', 'UserController@isValidId');
Route::get('password_reminder', 'UserController@passwordReminder');

Route::middleware(['auth:api'])->group(function () {
    /* User related */
    Route::get('/user', 'UserController@show');
    
    /* Groups */
    Route::get('/groups', 'GroupController@index');
    Route::get('/groups/{group}', 'GroupController@show')->middleware('member');
    Route::post('/groups', 'GroupController@store');
    Route::put('/groups/{group}', 'GroupController@update')->middleware('member');
    Route::delete('/groups/{group}', 'GroupController@delete')->middleware('member');
    
    /* Members */
    Route::post('/groups/{group}/members', 'GroupController@addMember');
    Route::put('/groups/{group}/members', 'GroupController@updateMember')->middleware('member'); 
    Route::put('/groups/{group}/admins', 'GroupController@updateAdmin')->middleware('member'); 
    Route::delete('/groups/{group}/members', 'GroupController@deleteMember')->middleware('member');

    /* Transactions */
    Route::get('/transactions', 'TransactionController@index')->middleware('member');
    Route::post('/transactions', 'TransactionController@store')->middleware('member');
    Route::put('/transactions/{purchase}', 'TransactionController@update')->middleware('owner:purchase');
    Route::delete('/transactions/{purchase}', 'TransactionController@delete')->middleware('owner:purchase');

    /* Payments */
    Route::get('/payments', 'PaymentController@index')->middleware('member');
    Route::post('/payments', 'PaymentController@store')->middleware('member');
    Route::put('/payments/{payment}', 'PaymentController@update')->middleware('owner:payment');
    Route::delete('/payments/{payment}', 'PaymentController@delete')->middleware('owner:payment');

    /* Requests*/
    Route::get('/requests', 'RequestController@index')->middleware('member');
    Route::post('/requests', 'RequestController@store')->middleware('member');
    Route::put('/requests/{shopping_request}', 'RequestController@fulfill')->middleware('member');
    Route::delete('/requests/{shopping_request}', 'RequestController@delete')->middleware('owner:request');
});
