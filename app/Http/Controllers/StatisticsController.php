<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Group;

class StatisticsController extends Controller
{
    public function payments(Request $request, Group $group)
    {
        $this->authorize('viewStatistics', $group);
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'until_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $payments_collection = $group->payments()
            ->whereBetween('created_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();


        $payed = $taken = [];
        $current_date = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->format('Y-m-d');
            $current_payments = $payments_collection
                ->whereBetween('created_at', [
                    $current_date->format('Y-m-d'),
                    $current_date->addDay()->format('Y-m-d')
                ]);

            $payed[$date] = round($current_payments->where('payer_id', $user_id)->sum('amount'), 2);
            $taken[$date] = round($current_payments->where('taker_id', $user_id)->sum('amount'), 2);
        }

        return response()->json(['data' => [
            'payed' => $payed,
            'taken' => $taken,
            'sum' => [
                'payed' => round(array_sum($payed), 2),
                'taken' => round(array_sum($taken), 2),
            ]
        ]]);
    }

    public function purchases(Request $request, Group $group)
    {
        $this->authorize('viewStatistics', $group);
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'until_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $purchases_collection = $group->purchases()
            ->whereBetween('created_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();


        $bought = $received = [];
        $current_date = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->format('Y-m-d');
            $current_purchases = $purchases_collection
                ->whereBetween('created_at', [
                    $current_date->format('Y-m-d'),
                    $current_date->addDay()->format('Y-m-d')
                ]);

            $bought[$date] = round($current_purchases->where('buyer_id', $user_id)->sum('amount'), 2);
            $received[$date] = round($current_purchases->where('receiver_id', $user_id)->sum('amount'), 2);
        }

        return response()->json(['data' => [
            'bought' => $bought,
            'received' => $received,
            'sum' => [
                'bought' => round(array_sum($bought), 2),
                'received' => round(array_sum($received), 2),
            ]
        ]]);
    }

    public function all(Request $request, Group $group)
    {
        $this->authorize('viewStatistics', $group);
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'until_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $purchases_collection = $group->purchases()
            ->whereBetween('created_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $payments_collection = $group->payments()
        ->whereBetween('created_at', [
            $from_date->format('Y-m-d'),
            $until_date->addDay()->format('Y-m-d')
        ])
        ->get();

        $payments = $purchases = [];
        $current_date   = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->format('Y-m-d');
            $current_purchases = $purchases_collection
                ->whereBetween('created_at', [
                    $current_date->format('Y-m-d'),
                    $current_date->addDay()->format('Y-m-d')
                ]);
            $current_payments = $payments_collection
            ->whereBetween('created_at', [
                $current_date->format('Y-m-d'),
                $current_date->addDay()->format('Y-m-d')
            ]);

            $payments[$date] = round($current_payments->sum('amount'), 2);
            $purchases[$date] = round($current_purchases->sum('amount'), 2);
        }

        return response()->json(['data' => [
            'payments' => $payments,
            'purchases' => $purchases,
            'sum' => [
                'payments' => round(array_sum($payments), 2),
                'purchases' => round(array_sum($purchases), 2),
            ]
        ]]);
    }
}
