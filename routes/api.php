<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

Route::post('register', 'UserController@register');
Route::post('login', 'Auth\LoginController@login');
Route::get('password_reminder', 'UserController@passwordReminder');

Route::middleware(['auth:api'])->group(function () {
    /* Auth */
    Route::get('/user', 'UserController@show');
    Route::post('logout', 'Auth\LoginController@logout');
    Route::post('change_password', 'UserController@changePassword');
    Route::post('change_username', 'UserController@changeUsername');
    Route::post('change_language', 'UserController@changeLanguage');

    /* Groups */
    Route::get('/groups', 'GroupController@index');
    Route::post('/groups', 'GroupController@store');
    Route::post('/join', 'GroupController@addMember');

    Route::middleware(['member'])->group(function () {
        /* Groups */
        Route::get('/groups/{group}', 'GroupController@show');
        Route::put('/groups/{group}', 'GroupController@update');
        Route::delete('/groups/{group}', 'GroupController@delete');

        /* Members */
        Route::get('/groups/{group}/member', 'GroupController@indexMember');
        Route::put('/groups/{group}/members', 'GroupController@updateMember');
        Route::put('/groups/{group}/admins', 'GroupController@updateAdmin');
        Route::post('/groups/{group}/members/delete', 'GroupController@deleteMember');

        /* Guests */
        Route::post('/groups/{group}/add_guest', 'GroupController@addGuest');
        Route::post('/group/{group}/merge_guest', 'GroupController@mergeGuest');

        /* 'I'm shopping' notification */
        Route::post('/groups/{group}/send_shopping_notification', 'GroupController@sendShoppingNotification');
    });

    /* Purchases */
    Route::get('/transactions', 'PurchaseController@index')->middleware('member');
    Route::post('/transactions', 'PurchaseController@store')->middleware('member');
    Route::put('/transactions/{purchase}', 'PurchaseController@update')->middleware('owner:purchase');
    Route::delete('/transactions/{purchase}', 'PurchaseController@delete')->middleware('owner:purchase');

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
Route::post('/bug', function (Request $request) {
    Mail::to(env('ADMIN_EMAIL'))->send(new App\Mail\ReportBug(Auth::guard('api')->user(), $request->description));
    Mail::to(env('DEVELOPER_EMAIL'))->send(new App\Mail\ReportBug(Auth::guard('api')->user(), $request->description));
    return response()->json(null, 204);
});

/**
 * Returns if the client app version is supported by the server
 */
Route::get('/supported', function (Request $request) {
    return response()->json($request->version >= env('SUPPORTED_APP_VERSION', 17));
});
