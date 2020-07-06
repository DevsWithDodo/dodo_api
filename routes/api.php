<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;


Route::post('register', 'Auth\RegisterController@register');
Route::post('register_email', 'Auth\RegisterController@registerEmail');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->middleware('auth:api');


/* Groups */
Route::get('/groups', 'GroupController@index');
Route::get('/groups/{group}', 'GroupController@show');
Route::get('/groups/{group}/refresh', 'GroupController@refreshBalances'); //for testing

/* User related getters */
Route::get('/users', function(){ return User::all(); }); //for testing
Route::get('/users/id/{user}', 'UserController@showById');
Route::get('/users/mail/{email}', 'UserController@showByMail');
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

