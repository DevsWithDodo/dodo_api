<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/* Groups */
Route::get('/groups', 'GroupController@index');
Route::get('/groups/{group}', 'GroupController@show');
//for testing:
Route::get('/groups/{group}/refresh', 'GroupController@refreshBalances');

/* User related getters */
Route::get('/users/{user}/balance', 'UserController@balance');
Route::get('/users/{user}/groups/{group}/balance', 'UserController@balanceInGroup');
Route::get('/users/{user}/groups', 'UserController@indexGroups');
Route::get('/users/{user}/groups/{group}', 'UserController@showGroup');

Route::get('/users/{user}/history', 'UserController@indexHistory');

Route::get('/users/{user}/groups/{group}/transactions/buyed', 'UserController@indexTransactionsBuyedInGroup');
Route::get('/users/{user}/groups/{group}/transactions/received', 'UserController@indexTransactionsReceivedInGroup');

Route::get('/users/{user}/groups/{group}/payments/payed', 'UserController@indexPaymentsPayedInGroup');
Route::get('/users/{user}/groups/{group}/payments/taken', 'UserController@indexPaymentsTakenInGroup');

/* Transactions */
Route::get('/transactions', 'TransactionController@index');
Route::get('/transactions/{purchase}', 'TransactionController@show');
Route::post('/transactions', 'TransactionController@store');
Route::put('/transactions/{purchase}', 'TransactionController@update');
Route::delete('/transactions/{purchase}', 'TransactionController@delete');

