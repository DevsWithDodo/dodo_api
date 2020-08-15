<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

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
    Route::post('/join', 'GroupController@addMember');
    Route::get('/groups/{group}/member', 'GroupController@indexMember')->middleware('member');
    Route::put('/groups/{group}/members', 'GroupController@updateMember')->middleware('member'); 
    Route::put('/groups/{group}/admins', 'GroupController@updateAdmin')->middleware('member'); 
    Route::delete('/groups/{group}/members', 'GroupController@deleteMember')->middleware('member');

    /* Invitations */
    Route::post('/invitations', 'InvitationController@store')->middleware('member');
    Route::delete('/invitations/{invitation}', 'InvitationController@delete');

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
    Route::put('/requests/{shopping_request}', 'RequestController@fulfill');
    Route::delete('/requests/{shopping_request}', 'RequestController@delete')->middleware('owner:request');
});


/**
 * Bug report to admin's email.
 */
Route::post('/bug', function(Request $request) {
    Mail::to(env('ADMIN_EMAIL'))->send(new App\Mail\ReportBug(Auth::guard('api')->user(), $request->description));
    return response()->json(null, 204);
});
/**
 * Returns if the client app version is supported by the server
 */
Route::get('/supported', function(Request $request) {
    return response()->json($request->version >= env('SUPPORTED_APP_VERSION', 17));
});