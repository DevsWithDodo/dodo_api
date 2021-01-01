<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::post('register',         [UserController::class, 'register']);
Route::post('login',            [LoginController::class, 'login']);
Route::get('password_reminder', [UserController::class, 'passwordReminder']);

Route::middleware(['auth:api'])->group(function () {
    /* Auth */
    Route::get('user',              [UserController::class, 'show']);
    Route::post('logout',           [LoginController::class, 'logout']);
    Route::post('change_password',  [UserController::class, 'changePassword']);
    Route::post('change_username',  [UserController::class, 'changeUsername']);
    Route::post('change_language',  [UserController::class, 'changeLanguage']);

    /* Groups */
    Route::get('groups',   [GroupController::class, 'index']);
    Route::post('groups',  [GroupController::class, 'store']);
    Route::post('join',    [MemberController::class, 'store']);

    Route::middleware(['member'])->group(function () {
        /* Groups */
        Route::get('groups/{group}',    [GroupController::class, 'show']);
        Route::put('groups/{group}',    [GroupController::class, 'update']);
        Route::delete('groups/{group}', [GroupController::class, 'delete']);

        /* Members */
        Route::get('groups/{group}/member',          [MemberController::class, 'index']);
        Route::put('groups/{group}/members',         [MemberController::class, 'update']);
        Route::put('groups/{group}/admins',          [MemberController::class, 'updateAdmin']);
        Route::post('groups/{group}/members/delete', [MemberController::class, 'delete']);

        /* Guests */
        Route::get('/groups/{group}/has_guests',    [MemberController::class, 'hasGuests']);
        Route::post('/groups/{group}/add_guest',    [MemberController::class, 'addGuest']);
        Route::post('/groups/{group}/merge_guest',  [MemberController::class, 'mergeGuest']);

        /* 'I'm shopping' notification */
        Route::post('/groups/{group}/send_shopping_notification', [GroupController::class, 'sendShoppingNotification']);
    });

    /* Purchases */
    Route::get('/transactions',               [PurchaseController::class, 'index'])->middleware('member');
    Route::post('/transactions',              [PurchaseController::class, 'store'])->middleware('member');
    Route::put('/transactions/{purchase}',    [PurchaseController::class, 'update']);
    Route::delete('/transactions/{purchase}', [PurchaseController::class, 'delete']);

    /* Payments */
    Route::get('/payments',              [PaymentController::class, 'index'])->middleware('member');
    Route::post('/payments',             [PaymentController::class, 'store'])->middleware('member');
    Route::put('/payments/{payment}',    [PaymentController::class, 'update']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'delete']);

    /* Requests */
    Route::get('/requests',                       [RequestController::class, 'index'])->middleware('member');
    Route::post('/requests',                      [RequestController::class, 'store'])->middleware('member');
    Route::put('/requests/{shopping_request}',    [RequestController::class, 'update']);
    Route::delete('/requests/{shopping_request}', [RequestController::class, 'delete']);
});


/**
 * Bug report to admin's email.
 */
Route::post('/bug', function (Request $request) {
    Mail::to(env('ADMIN_EMAIL'))->send(new App\Mail\ReportBug(auth('api')->user(), $request->description));
    Mail::to(env('DEVELOPER_EMAIL'))->send(new App\Mail\ReportBug(auth('api')->user(), $request->description));
    return response()->json(null, 204);
});

/**
 * Returns if the client app version is supported by the server
 */
Route::get('/supported', function (Request $request) {
    return response()->json($request->version >= env('SUPPORTED_APP_VERSION', 17));
});
