<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Group;
use App\Transactions\PurchaseReceiver;
use Illuminate\Validation\Rule;

class StatisticsController extends Controller
{
    public function payments(Request $request, Group $group)
    {
        $this->authorize('viewStatistics', $group);
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'until_date' => 'required|date_format:Y-m-d',
            'category' => ['nullable', Rule::in($group->categories)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $payments_collection = $group->payments();
        if(isset($request->category))
            $payments_collection->where('category', $request->category);
        $payments_collection
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();


        $payed = $taken = [];
        $current_date = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->format('Y-m-d');
            $current_payments = $payments_collection
                ->whereBetween('updated_at', [
                    $current_date->format('Y-m-d'),
                    $current_date->addDay()->format('Y-m-d')
                ]);

            $payed[$date] = $current_payments->where('payer_id', $user_id)->sum('amount');
            $taken[$date] = $current_payments->where('taker_id', $user_id)->sum('amount');
        }

        return response()->json(['data' => [
            'payed' => $payed,
            'taken' => $taken,
            'sum' => [
                'payed' => array_sum($payed),
                'taken' => array_sum($taken),
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
            'category' => ['nullable', Rule::in($group->categories)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $purchases_collection = $group->purchases();
        if(isset($request->category))
            $purchases_collection->where('category', $request->category);
        $purchases_collection
            ->where('buyer_id', $user_id)
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->select(['amount', 'updated_at'])
            ->get();
        $receivers_collection = PurchaseReceiver::join('purchases', 'purchases.id', '=', 'purchase_receivers.purchase_id')
            ->where('receiver_id', $user_id)
            ->where('purchases.group_id', $group->id)
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->select(['purchase_receivers.amount', 'updated_at'])
            ->get();

        $bought = $received = [];
        $current_date = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->toImmutable();

            $bought[$date->format('Y-m-d')] = $purchases_collection
                ->whereBetween('updated_at', [
                    $date->format('Y-m-d'),
                    $date->addDay()->format('Y-m-d')
                ])
                ->sum('amount');

            $received[$date->format('Y-m-d')] = $receivers_collection
                ->whereBetween('updated_at', [
                    $date->format('Y-m-d'),
                    $date->addDay()->format('Y-m-d')
                ])
                ->sum('amount');
            $current_date->addDay();
        }

        return response()->json(['data' => [
            'bought' => $bought,
            'received' => $received,
            'sum' => [
                'bought' => array_sum($bought),
                'received' => array_sum($received)
            ]
        ]]);
    }

    public function all(Request $request, Group $group)
    {
        $this->authorize('viewStatistics', $group);
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'until_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $purchases_collection = $group->purchases()
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $payments_collection = $group->payments()
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $payments = $purchases = [];
        $current_date   = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->toImmutable();

            $payments[$date->format('Y-m-d')] = $payments_collection
                ->whereBetween('updated_at', [
                    $date->format('Y-m-d'),
                    $date->addDay()->format('Y-m-d')
                ])
                ->sum('amount');

            $purchases[$date->format('Y-m-d')] = $purchases_collection
                ->whereBetween('updated_at', [
                    $date->format('Y-m-d'),
                    $date->addDay()->format('Y-m-d')
                ])
                ->sum('amount');
            $current_date->addDay();
        }

        return response()->json(['data' => [
            'payments' => $payments,
            'purchases' => $purchases,
            'sum' => [
                'payments' => array_sum($payments),
                'purchases' => array_sum($purchases),
            ]
        ]]);
    }
}
