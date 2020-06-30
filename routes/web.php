<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function(){
    $user = App\User::find(1);
    echo $user->name;
    echo "\n";
    foreach ($user->groups as $group) {
        echo $group->name;
        echo "\n";
        echo $group->member_data->nickname;
        echo "\n";
    }
});
