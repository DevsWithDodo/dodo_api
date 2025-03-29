<?php

namespace App\Http\Controllers;

use App\Group;
use App\User;
use Auth;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiAdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
            "remember" => "boolean"
        ]);

        $credentials = $request->only("email", "password");

        if (Auth::guard('web')->attempt($credentials, true)) {
            return Auth::guard('web')->user();
        }

        return response([
            "code" => "invalid_credentials",
            "message" => "Invalid credentials",
            "errors" => [
                "email" => "login.invalidCredentials",
                "password" => "login.invalidCredentials"
            ]
        ], 401);
    }

    public function logout()
    {
        Auth::guard("web")->logout();
    }

    public function show()
    {
        return Auth::guard("web")->user();
    }

    public function statistics(Request $request)
    {
        $user = Auth::guard("web")->user();
        if (!$user) {
            return response([
                "code" => "unauthorized",
                "message" => "Unauthorized",
            ], 401);
        }

        $endDate = $request->input('end_date', Carbon::now());
        $startDate = $request->input('start_date', Carbon::now()->subDays(30));
        $endDate = Carbon::parse($endDate)->endOfDay();
        $startDate = Carbon::parse($startDate)->startOfDay();
        if ($endDate->lessThan($startDate)) {
            return response([
                "code" => "invalid_date_range",
                "message" => "Invalid date range",
            ], 400);
        }

        $newUsers = DB::table('users')
            ->where(
                fn ($query) => $query
                    ->where('password', '!=', null)
                    ->orWhere('google_id', '!=', null)
                    ->orWhere('apple_id', '!=', null)
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as new_user_count')])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');
        $appOpened = DB::table('app_opened_events')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as app_opened_count')])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Group activity

        $purchases = DB::table('purchases')
            ->select(DB::raw("group_id, DATE(updated_at) as activity_date"))
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $payments = DB::table('payments')
            ->select(DB::raw("group_id, DATE(updated_at) as activity_date"))
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $requests = DB::table('requests')
            ->select(DB::raw("group_id, DATE(updated_at) as activity_date"))
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $reactions = DB::table('reactions')
            ->select(DB::raw("group_id, DATE(updated_at) as activity_date"))
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $unionQuery = $purchases
            ->unionAll($payments)
            ->unionAll($requests)
            ->unionAll($reactions);

        $activeGroups = DB::query()
            ->fromSub($unionQuery, 'activities')
            ->select('activity_date', DB::raw('COUNT(DISTINCT group_id) as active_group_count'))
            ->groupBy('activity_date')
            ->orderBy('activity_date', 'asc')
            ->get()
            ->keyBy('activity_date');

        $newGroups = DB::table('groups')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as new_group_count')])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');


        $period = CarbonPeriod::create($startDate, $endDate);
        $groupCounts = [];
        $userCounts = [];

        // Merge the query result with the date range; if a date is missing, assign zero.
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $groupCounts[] = [
                "date" => $dateString,
                "active_count" => $activeGroups->get($dateString)->active_group_count ?? 0,
                "new_count" => $newGroups->get($dateString)->new_group_count ?? 0,
            ];
            $userCounts[] = [
                "date" => $dateString,
                "new_count" => $newUsers->get($dateString)->new_user_count ?? 0,
                "app_opened_count" => $appOpened->get($dateString)->app_opened_count ?? 0,
            ];
        }
        
        $userQuery = User::query()
            ->where(fn ($query) => $query
                ->where('password', '!=', null)
                ->orWhere('google_id', '!=', null)
                ->orWhere('apple_id', '!=', null)
            );
        // Where has more purchases than 2
        $colorThemeCountUsedApp = $userQuery
            ->has('purchases', '>=', 2)
            ->select(['color_theme', DB::raw('COUNT(*) as count')])
            ->groupBy('color_theme')
            ->orderBy('count', 'desc')
            ->get();
        $colorThemeCountUsedApp = $colorThemeCountUsedApp->map(function ($item) {
            return [
                "color_theme" => $item->color_theme,
                "count" => $item->count,
            ];
        });

        $purchases = DB::table('purchases')
            ->select(['group_id', 'created_at'])
            ->get()
            ->groupBy('group_id');

        $stds = [];
        // Calculate the standard deviation for each group
        foreach ($purchases as $groupId => $groupPurchases) {
            $purchaseDates = $groupPurchases->pluck('created_at')->map(function ($date) {
                return Carbon::parse($date)->timestamp;
            })->toArray();

            if (count($purchaseDates) > 1) {
                $mean = array_sum($purchaseDates) / count($purchaseDates);
                $squaredDiffs = array_map(function ($x) use ($mean) {
                    return pow($x - $mean, 2);
                }, $purchaseDates);
                $stdDev = sqrt(array_sum($squaredDiffs) / count($squaredDiffs));
                // Convert to days
                $stdDev /= (60 * 60 * 24);
                if ($stdDev < 0.003) {
                    continue;
                }
                $stds[] = $stdDev;
            }
        }
        // Histogram with binsize = 1 day
        $max_std = ceil(max($stds));
        $min_std = floor(min($stds));
        $bins = [];
        $binSize = 7; // N days
        for ($i = $min_std; $i <= $max_std; $i += $binSize) {
            $bins[$i] = 0;
        }
        foreach ($stds as $std) {
            $binIndex = floor($std / $binSize) * $binSize;
            if (isset($bins[$binIndex])) {
                $bins[$binIndex]++;
            }
        }

        // Convert bins to array
        $stds = [];
        foreach ($bins as $bin => $count) {
            $stds[] = [
                "bin" => $bin + $binSize,
                "count" => $count,
            ];
        }



        return response([
            "user_counts" => $userCounts,
            "group_counts" => $groupCounts,
            "color_theme_count" => $colorThemeCountUsedApp,
            "total_users" => $colorThemeCountUsedApp->sum('count'),
            "purchase_standard_deviation" => $stds,
        ]);
    }
}
