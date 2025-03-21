<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Group;
use App\Http\Controllers\AdminController;
use App\Mail\AdminAccess;

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

Route::fallback(fn() => view("app"));