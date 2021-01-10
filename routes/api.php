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

Route::post('register',         [UserController::class, 'register'])->name('user.register');
Route::post('login',            [LoginController::class, 'login'])->name('user.login');
Route::get('password_reminder', [UserController::class, 'passwordReminder'])->name('user.password_reminder');

Route::middleware(['auth:api'])->group(function () {
    /* Auth */
    Route::get('user',              [UserController::class, 'show'])->name('user.show');
    Route::post('logout',           [LoginController::class, 'logout'])->name('user.logout');
    //TODO merge update routes
    Route::post('change_password',  [UserController::class, 'changePassword']);
    Route::post('change_username',  [UserController::class, 'changeUsername']);
    Route::post('change_language',  [UserController::class, 'changeLanguage']);

    /* Groups */
    Route::get('groups',   [GroupController::class, 'index'])->name('group.index');
    Route::post('groups',  [GroupController::class, 'store'])->name('group.store');
    Route::post('join',    [MemberController::class, 'store'])->name('member.store');

    //TODO delete middleware
    Route::middleware(['member'])->group(function () {
        /* Groups */
        Route::get('groups/{group}',    [GroupController::class, 'show'])->name('group.show');
        Route::put('groups/{group}',    [GroupController::class, 'update'])->name('group.update');
        Route::delete('groups/{group}', [GroupController::class, 'delete'])->name('group.delete');

        /* Members */
        Route::get('groups/{group}/member',          [MemberController::class, 'index'])->name('member.index');
        Route::put('groups/{group}/members',         [MemberController::class, 'update'])->name('member.update');
        Route::put('groups/{group}/admins',          [MemberController::class, 'updateAdmin'])->name('member.admin.update');
        Route::post('groups/{group}/members/delete', [MemberController::class, 'delete'])->name('member.delete');

        /* Guests */
        Route::get('/groups/{group}/has_guests',    [MemberController::class, 'hasGuests'])->name('guests.has_guests');
        Route::post('/groups/{group}/add_guest',    [MemberController::class, 'addGuest'])->name('guests.store');
        Route::post('/groups/{group}/merge_guest',  [MemberController::class, 'mergeGuest'])->name('guests.merge');

        /* 'I'm shopping' notification */
        Route::post('/groups/{group}/send_shopping_notification', [GroupController::class, 'sendShoppingNotification'])->name('notification.shopping');
    });

    /* Purchases */
    //TODO delete middleware
    Route::get('/purchases',               [PurchaseController::class, 'index'])->middleware('member')->name('purchases.index');
    Route::post('/purchases',              [PurchaseController::class, 'store'])->middleware('member')->name('purchases.store');
    Route::put('/purchases/{purchase}',    [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'delete'])->name('purchases.delete');

    Route::post('/purchases/reaction',              [PurchaseController::class, 'add_reaction'])->name('reactions.purchases.add');
    Route::delete('/purchases/reaction/{reaction}', [PurchaseController::class, 'remove_reaction'])->name('reactions.purchases.remove');

    //for backward compatibility //TODO delete
    Route::get('/transactions',               [PurchaseController::class, 'index'])->middleware('member');
    Route::post('/transactions',              [PurchaseController::class, 'store'])->middleware('member');
    Route::put('/transactions/{purchase}',    [PurchaseController::class, 'update']);
    Route::delete('/transactions/{purchase}', [PurchaseController::class, 'delete']);

    /* Payments */
    Route::get('/payments',              [PaymentController::class, 'index'])->middleware('member')->name('payments.index');
    Route::post('/payments',             [PaymentController::class, 'store'])->middleware('member')->name('payments.store');
    Route::put('/payments/{payment}',    [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'delete'])->name('payments.delete');

    Route::post('/payments/reaction',              [PaymentController::class, 'add_reaction'])->name('reactions.payments.add');
    Route::delete('/payments/reaction/{reaction}', [PaymentController::class, 'remove_reaction'])->name('reactions.payments.remove');

    /* Requests */
    Route::get('/requests',                       [RequestController::class, 'index'])->middleware('member')->name('requests.index');
    Route::post('/requests',                      [RequestController::class, 'store'])->middleware('member')->name('requests.store');
    Route::put('/requests/{shopping_request}',    [RequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{shopping_request}', [RequestController::class, 'delete'])->name('requests.delete');

    Route::post('/requests/reaction',              [RequestController::class, 'add_reaction'])->name('reactions.requests.add');
    Route::delete('/requests/reaction/{reaction}', [RequestController::class, 'remove_reaction'])->name('reactions.requests.remove');
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
