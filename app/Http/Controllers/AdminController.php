<?php

namespace App\Http\Controllers;

use App\Group;
use App\Mail\AdminAccess;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mail;
use URL;

class AdminController extends Controller {
    public function index(Request $request) {
        if (!$request->hasValidSignature()) {
            return view('admin', [
                'hasValidSignature' => false,
            ]);
        }
        $not_boosted_members = $boosted_members = 0;
        $boosted = $not_boosted = $all_groups = 0;
        $payments = $purchases = 0;
        $groups_use_requests = $requests_all = 0;
        $groups_use_guests = $guests_all = 0;
        $one_member_only = Group::has('members', '=', 1)->count();
        $groupQuery = Group::has('members', '>=', 2);
        $payments = Payment::whereHas('group', function ($query) {
            $query->has('members', '>=', 2);
        })->count();
        $purchases = Purchase::whereHas('group', function ($query) {
            $query->has('members', '>=', 2);
        })->count();
        $boosted_members = with(clone $groupQuery)->where('boosted', true)->withCount('members')->get()->sum('members_count');
        $not_boosted_members = with(clone $groupQuery)->where('boosted', false)->withCount('members')->get()->sum('members_count');
        $boosted = with(clone $groupQuery)->where('boosted', true)->count();
        $not_boosted = with(clone $groupQuery)->where('boosted', false)->count();
        $all_groups = with(clone $groupQuery)->count();
        $requests_all = with(clone $groupQuery)->withCount('requests')->get()->sum('requests_count');
        $groups_use_requests = with(clone $groupQuery)->has('requests')->count();
        $guests_all = with(clone $groupQuery)->withCount('guests')->get()->sum('guests_count');
        $groups_use_guests = with(clone $groupQuery)->has('guests')->count();
        $currencies = with(clone $groupQuery)->groupBy('currency')->selectRaw('currency, count(*) as count')->get()->pluck('count', 'currency')->toArray();

        arsort($currencies);

        $zero_group = User::has('groups', '=', 0)->where('password', '<>', null)->count();
        $userQuery = User::has('groups', '>=', 1)->where('password', '<>', null);
        $all_users = $userQuery->count();
        $activeEverQuery=User::activeUserQuery(-1);
        $activeLastDay = User::activeUserQuery(1)->where('password', '<>', null)->count();
        $activeLast7 = User::activeUserQuery(7)->where('password', '<>', null)->count();
        $activeLast30 = User::activeUserQuery()->where('password', '<>', null)->count();
        $activeLast365 = User::activeUserQuery(365)->where('password', '<>', null)->count();
        $activeEver = with(clone $activeEverQuery)->where('password', '<>', null)->count();
        $guests = User::where('password', null)->count();
        $groups = 0;
        $languages = [];
        $group_count = [];
        $colors_gradients_enabled = [];
        $colors_free = [];

        $groups = with(clone $userQuery)->withCount('groups')->get()->sum('groups_count');
        $group_count = with(clone $userQuery)->withCount('groups')->get()->countBy('groups_count')->toArray();


        $users_no_gradient = with(clone $activeEverQuery)->where('password', '<>', null)->where('gradients_enabled', false)->count();
        $users_gradient = with(clone $activeEverQuery)->where('password', '<>', null)->where('gradients_enabled', true)->count();
        $colors_gradients_enabled = with(clone $activeEverQuery)
            ->where('password', '<>', null)
            ->where('gradients_enabled', true)
            ->groupBy('color_theme')
            ->selectRaw('color_theme, count(*) as count')
            ->get()
            ->pluck('count', 'color_theme')
            ->toArray();
        $colors_free = with(clone $activeEverQuery)
            ->where('password', '<>', null)
            ->where('gradients_enabled', false)
            ->where('color_theme', 'not like', '%gradient%')
            ->groupBy('color_theme')
            ->selectRaw('color_theme, count(*) as count')
            ->get()
            ->pluck('count', 'color_theme')
            ->toArray();
        $darkTheme = with(clone $activeEverQuery)
            ->where('password', '<>', null)
            ->where('color_theme', 'like', '%dark%')
            ->count();
        $lightTheme = with(clone $activeEverQuery)
            ->where('password', '<>', null)
            ->where('color_theme', 'like', '%light%')
            ->count();

        $languages = with(clone $userQuery)
            ->groupBy('language')
            ->selectRaw('language, count(*) as count')
            ->get()
            ->pluck('count', 'language')
            ->toArray();
        asort($languages);
        ksort($group_count);
        arsort($languages);
        arsort($colors_gradients_enabled);
        arsort($colors_free);
        //groups
        $boosted_members_avg = round($boosted_members / ($boosted ? $boosted : 1), 2);
        $not_boosted_members_avg = round($not_boosted_members / ($not_boosted ? $not_boosted : 1), 2);
        $payments_avg = round($payments / ($all_groups ? $all_groups : 1), 2);
        $purchases_avg = round($purchases / ($all_groups ? $all_groups : 1), 2);
        $requests_all_avg = round($requests_all / ($all_groups ? $all_groups : 1), 2);
        $requests_avg = round($requests_all / ($groups_use_requests ? $groups_use_requests : 1), 2);
        $guests_all_avg = round($guests_all / ($all_groups ? $all_groups : 1), 2);
        $guests_avg = round($guests_all / ($groups_use_guests ? $groups_use_guests : 1), 2);
        //users
        $group_avg = round($groups / ($all_users ? $all_users : 1), 2);

        return view('admin', [
            'hasValidSignature' => true,
            'one_member_only' => $one_member_only,
            'all_groups' => $all_groups,
            'all_users' => $all_users,
            'boosted' => $boosted,
            'boosted_members_avg' => $boosted_members_avg,
            'not_boosted_members_avg' => $not_boosted_members_avg,
            'guests_all_avg' => $guests_all_avg,
            'guests_avg' => $guests_avg,
            'groups_use_guests' => $groups_use_guests,
            'purchases_avg' => $purchases_avg,
            'payments_avg' => $payments_avg,
            'requests_all_avg' => $requests_all_avg,
            'requests_avg' => $requests_avg,
            'groups_use_requests' => $groups_use_requests,
            'currencies' => $currencies,
            'zero_group' => $zero_group,
            'guests' => $guests,
            'group_count' => $group_count,
            'group_avg' => $group_avg,
            'languages' => $languages,
            'colors_gradients_enabled' => $colors_gradients_enabled,
            'colors_free' => $colors_free,
            'users_gradient' => $users_gradient,
            'users_no_gradient' => $users_no_gradient,
            'activeLastDay' => $activeLastDay,
            'activeLast7' => $activeLast7,
            'activeLast30' => $activeLast30,
            'activeLast365' => $activeLast365,
            'activeEver' => $activeEver,
            'darkTheme' => $darkTheme,
            'lightTheme' => $lightTheme,
        ]);
    }

    public function sendAccessMail() {
        $url = URL::temporarySignedRoute('admin.index', now()->addMinutes(30));
        if(env('APP_DEBUG') || env('DEVELOPER_EMAIL', null)==null){
            return $url;
        }
        Mail::to(config('app.admin_email'))->send(new AdminAccess($url));
        Mail::to(config('app.developer_email'))->send(new AdminAccess($url));
        return response("Secure link sent to the developer emails.");
    }
}
