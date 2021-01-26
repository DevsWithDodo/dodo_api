<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Notifications\CustomNotification;

use App\Group;
use App\Http\Controllers\CurrencyController;
use App\User;
use Illuminate\Support\Facades\Log;

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

Route::get('/privacy-policy', function () {
    return view('privacy_policy');
});

Route::get('/join/{token}', function ($token) {
    return view('join', ['group' => Group::firstWhere('invitation', $token)]);
});

Route::get('/admin', function () {
    $not_boosted_members = $boosted_members = 0;
    $not_boosted_guests = $boosted_guests = 0;
    $boosted = $not_boosted = $all = 0;
    $payments = $purchases = 0;
    $groups_use_requests = $requests_all = $requests_use = 0;
    $groups_use_guests = $guests_all = $guests_use = 0;
    $one_member_only = Group::has('members', '=', 1)->count();
    $groups = Group::has('members', '>=', 2)->get();
    $currencies = [];
    foreach ($groups as $group) {
        if ($group->boosted) {
            $boosted_members += $group->members()->count();
            $boosted++;
        } else {
            $not_boosted_members += $group->members()->count();
            $not_boosted++;
        }
        $all++;
        $payments += $group->payments()->count();
        $purchases += $group->purchases()->count();
        $requests= $group->requests()->count();
        $requests_all += $requests;
        if ($requests) {
            $requests_use += $requests;
            $groups_use_requests++;
        }
        $guests = $group->guests()->count();
        $guests_all += $guests;
        if ($guests){
            $guests_use += $guests;
            $groups_use_guests++;
        }
        if (isset($currencies[$group->currency])){
            $currencies[$group->currency]++;
        }else {
            $currencies[$group->currency] = 1;
        }
    }
    asort($currencies);

    $zero_group = User::has('groups', '=', 0)->where('password', '<>', null)->count();
    $users = User::has('groups', '>=', 1)->where('password', '<>', null)->with('groups')->get();
    $all_users = $users->count();
    $guests = User::where('password', null)->count();
    $groups = 0;
    $languages = [];
    $group_count = [];
    foreach ($users as $user) {
        $groups += $user->groups()->count();

        if (isset($group_count[$user->groups()->count()])){
            $group_count[$user->groups()->count()]++;
        }else {
            $group_count[$user->groups()->count()] = 1;
        }

        if (isset($languages[$user->language])){
            $languages[$user->language]++;
        }else {
            $languages[$user->language] = 1;
        }
    }
    asort($languages);
    arsort($group_count);
    return view('admin', [
        'not_boosted_members' => $not_boosted_members,
        'one_member_only' => $one_member_only, 2,
        'boosted' => $boosted,
        'not_boosted' => $not_boosted,
        'all_groups' => $all,
        'boosted_members_avg' => round($boosted_members / $boosted, 2),
        'not_boosted_members_avg' => round($not_boosted_members / $not_boosted, 2),
        'boosted_guests_avg' => round($boosted_guests / $boosted, 2),
        'not_boosted_guests_avg' => round($not_boosted_guests / $not_boosted, 2),
        'payments_avg' => round($payments / $all, 2),
        'purchases_avg' => round($purchases / $all, 2),
        'requests_all_avg' => round($requests_all / $all, 2),
        'requests_avg' => round($requests_use / ($groups_use_requests ? $groups_use_requests : 1), 2),
        'groups_use_requests' => $groups_use_requests,
        'guests_all_avg' => round($guests_all / $all, 2),
        'guests_avg' => round($guests_use / ($groups_use_guests ? $groups_use_guests : 1), 2),
        'groups_use_guests' => $groups_use_guests,
        'currencies' => $currencies,
        //users
        'zero_group' => $zero_group,
        'group_count' => $group_count,
        'all_users' => $all_users,
        'guests' => $guests,
        'group_avg' => round($groups / $all_users, 2),
        'languages' => $languages
    ]);

})->middleware('passwordprotect:1');

Route::post('/admin/recalculate', function (Request $request) {
    Group::findOrFail($request->group)->recalculateBalances();
    return redirect()->back();
})->middleware('passwordprotect:1');

Route::post('admin/send_notification', function (Request $request) {
    if ($request->everyone) {
        foreach (\App\User::all() as $user)
            $user->notify(new CustomNotification($request->message));
        return response("Message sent to everyone.");
    } else {
        $user = \App\User::findOrFail($request->id);
        $user->notify(new CustomNotification($request->message));
        return response("Message sent to " . $user->username . '.');
    }
})->middleware('passwordprotect:1');

Route::get('/landscape_preview', function () {
    $path = public_path() . '/lender_preview.png';

    if (!File::exists($path)) {
        return response()->json(['message' => 'Image not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Auth::routes();
