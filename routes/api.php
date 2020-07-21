<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* For testing */
Route::get('/transactions', function(){ return App\Http\Resources\Transaction::collection(App\Transactions\Purchase::all());}); //for testing
Route::get('/users', function(){ return App\User::all(); }); //for testing
Route::get('/groups_all', function() { return App\Http\Resources\Group::collection(App\Group::all()); });
//Route::get('/groups/{group}/refresh', 'GroupController@refreshBalances'); //for testing

/* Auth */
Route::post('register', 'Auth\RegisterController@register');
Route::post('register_email', 'Auth\RegisterController@registerEmail')->middleware('auth:api');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->middleware('auth:api');

Route::middleware(['auth:api'])->group(function () {
    /* User related */
    Route::get('/users/{user}', 'UserController@show');

    Route::get('/balance/all', 'UserController@balance');
    Route::get('/balance/group/{group}', 'UserController@balanceInGroup');

    Route::get('/history', 'UserController@indexHistory');
    
    /* Groups */
    Route::get('/groups', 'GroupController@index');
    Route::get('/groups/{group}', 'GroupController@show');
    Route::post('/groups', 'GroupController@store');
    Route::put('/groups/{group}', 'GroupController@update');
    Route::delete('/groups/{group}', 'GroupController@delete');
    
    /* Members */
    Route::post('/groups/{group}/members', 'GroupController@addMember');
    Route::put('/groups/{group}/members', 'GroupController@updateMember');
    //Route::delete('/groups/{group}/members', 'GroupController@deleteMember');

    /* Transactions */
    Route::get('/transactions_buyed', 'TransactionController@indexBuyedInGroup');
    Route::get('/transactions_received', 'TransactionController@indexReceivedInGroup');
    Route::get('/transactions/{purchase}', 'TransactionController@show');
    Route::post('/transactions', 'TransactionController@store');
    Route::put('/transactions/{purchase}', 'TransactionController@update');
    Route::delete('/transactions/{purchase}', 'TransactionController@delete');

    /* Payments */
    Route::get('/payments_payed', 'PaymentController@indexPayedInGroup');
    Route::get('/payments_taken', 'PaymentController@indexTakenInGroup');
    Route::get('/payments/{payment}', 'PaymentController@show');
    Route::post('/payments', 'PaymentController@store');
    Route::put('/payments/{payment}', 'PaymentController@update');
    Route::delete('/payments/{payment}', 'PaymentController@delete');

    /* Shopping Cart */
   /*  Route::get('/users/{user}/groups/{group}/shopping_cart', 'ShoppingCartController@indexInGroup');
    Route::get('/shopping_cart/{shopping_cart}', 'ShoppingCartController@show');
    Route::post('/shopping_cart', 'ShoppingCartController@store');
    Route::put('/shopping_cart/{shopping_cart}', 'ShoppingCartController@update');
    Route::delete('/shopping_cart/{shopping_cart}', 'ShoppingCartController@delete');
 */
});
