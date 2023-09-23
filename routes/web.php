<?php


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

use Illuminate\Support\Facades\Route;

Route::fallback(fn () => view('welcome'));

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/privacy-policy', function () {
//     return view('privacy_policy');
// });

// Route::get('/join/{token}', function ($token) {
//     return view('join', ['group' => Group::firstWhere('invitation', $token)]);
// });

// Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
// Route::get('/admin/send-access-mail', [AdminController::class, 'sendAccessMail'])->name('admin.send-access-mail');

// Route::get('/preview', function () {
//     $path = public_path() . '/preview.png';

//     if (!File::exists($path)) {
//         return response()->json(['message' => 'Image not found.'], 404);
//     }

//     $file = File::get($path);
//     $type = File::mimeType($path);

//     $response = Response::make($file, 200);
//     $response->header("Content-Type", $type);

//     return $response;
// });

// Auth::routes();
