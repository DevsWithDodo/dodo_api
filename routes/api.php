<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::post('register',         [UserController::class, 'register'])->name('user.register');
Route::post('login',            [UserController::class, 'login'])->name('user.login');
Route::get('password_reminder', [UserController::class, 'passwordReminder'])->name('user.password_reminder');


Route::middleware(['auth:api'])->group(function () {
    /* Auth */
    Route::get('user',     [UserController::class, 'show'])->name('user.show');
    Route::put('user',     [UserController::class, 'update'])->name('user.update');
    Route::post('logout',  [UserController::class, 'logout'])->name('user.logout');
    Route::delete('user',  [UserController::class, 'delete'])->name('user.delete');

    Route::get('balance',  [UserController::class, 'balance'])->name('user.balance');

    /* Groups */
    Route::post('groups',               [GroupController::class, 'store'])->name('group.store');
    Route::get('groups',                [GroupController::class, 'index'])->name('group.index');
    Route::get('groups/{group}',        [GroupController::class, 'show'])->name('group.show');
    Route::put('groups/{group}',        [GroupController::class, 'update'])->name('group.update');
    Route::delete('groups/{group}',     [GroupController::class, 'delete'])->name('group.delete');

    /* Boosts */
    Route::get('groups/{group}/boost', [GroupController::class, 'isBoosted'])->name('group.is_boosted');
    Route::post('groups/{group}/boost', [GroupController::class, 'boost'])->name('group.boost');

    /* Members */
    Route::post('join',                          [MemberController::class, 'store'])->name('member.store');
    Route::get('groups/{group}/member',          [MemberController::class, 'show'])->name('member.show');
    Route::put('groups/{group}/members',         [MemberController::class, 'update'])->name('member.update');
    Route::put('groups/{group}/admins',          [MemberController::class, 'updateAdmin'])->name('member.admin.update');
    Route::post('groups/{group}/members/delete', [MemberController::class, 'delete'])->name('member.delete');

    /* Guests */
    Route::get('/groups/{group}/has_guests',    [MemberController::class, 'hasGuests'])->name('guests.has_guests');
    Route::post('/groups/{group}/add_guest',    [MemberController::class, 'addGuest'])->name('guests.store');
    Route::post('/groups/{group}/merge_guest',  [MemberController::class, 'mergeGuest'])->name('guests.merge');

    /* Purchases */
    Route::get('/purchases',               [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/purchases',              [PurchaseController::class, 'store'])->name('purchases.store');
    Route::put('/purchases/{purchase}',    [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'delete'])->name('purchases.delete');

    Route::post('/purchases/reaction',     [PurchaseController::class, 'reaction'])->name('reactions.purchases');

    /* Payments */
    Route::get('/payments',              [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments',             [PaymentController::class, 'store'])->name('payments.store');
    Route::put('/payments/{payment}',    [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'delete'])->name('payments.delete');

    Route::post('/payments/reaction',    [PaymentController::class, 'reaction'])->name('reactions.payments');

    /* Requests */
    Route::get('/requests',                             [RequestController::class, 'index'])->name('requests.index');
    Route::post('/requests',                            [RequestController::class, 'store'])->name('requests.store');
    Route::put('/requests/{shopping_request}',          [RequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{shopping_request}',       [RequestController::class, 'delete'])->name('requests.delete');
    Route::post('/requests/restore/{shopping_request}', [RequestController::class, 'restore'])->name('requests.restore');

    Route::post('/requests/reaction',                   [RequestController::class, 'reaction'])->name('reactions.requests');

    /* 'I'm shopping' notification */
    Route::post('/groups/{group}/send_shopping_notification', [RequestController::class, 'sendShoppingNotification'])->name('notification.shopping');


    /* Statistics */
    Route::get('/groups/{group}/statistics/payments',  [StatisticsController::class, 'payments']);
    Route::get('/groups/{group}/statistics/purchases', [StatisticsController::class, 'purchases']);
    Route::get('/groups/{group}/statistics/all',       [StatisticsController::class, 'all']);


});


/**
 * Bug report to admin's email.
 */
Route::post('/bug', function (Request $request) {
    Mail::to(config('app.admin_email'))->send(new App\Mail\ReportBug(auth('api')->user(), $request->description));
    Mail::to(config('app.developer_email'))->send(new App\Mail\ReportBug(auth('api')->user(), $request->description));
    return response()->json(null, 204);
});

/**
 * Returns if the client app version is supported by the server
 */
Route::get('/supported', function (Request $request) {
    return response()->json($request->version >= config('app.supported_version', 17));
});
