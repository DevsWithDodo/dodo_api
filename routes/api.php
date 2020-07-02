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


Route::get('/groups', 'GroupController@index');
Route::get('/groups/{group}', 'GroupController@show');
Route::get('/groups/{group}/refresh', 'GroupController@refreshBalances');


Route::get('/users/{user}/balance', 'UserController@balance');
Route::get('/users/{user}/groups/{group}/balance', 'UserController@balanceInGroup');
Route::get('/users/{user}/groups', 'UserController@indexGroups');
Route::get('/users/{user}/groups/{group}', 'UserController@showGroup');


Route::get('/users/{user}/transactions', 'UserController@indexTransactions');
Route::get('/users/{user}/groups/{group}/transactions/buyed', 'UserController@indexTransactionsBuyedInGroup');
Route::get('/users/{user}/groups/{group}/transactions/received', 'UserController@indexTransactionsReceivedInGroup');


Route::get('/groups/{group}/transactions', 'TransactionController@index');
Route::get('/groups/{group}/transactions/{purchase}', 'TransactionController@show');
/*Route::post('/groups/{group}/transactions', 'TransactionController@store');
Route::put('/groups/{group}/transactions/{purchase}', 'TransactionController@update');
Route::delete('/groups/{group}/transactions/{purchase}', 'TransactionController@delete'); */