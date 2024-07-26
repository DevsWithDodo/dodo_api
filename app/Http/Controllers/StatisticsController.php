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
        $payments_collection = $payments_collection
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $payments_collection = $payments_collection->map(fn ($payment) => [
            'amount' => $payment->amount,
            'payer_id' => $payment->payer_id,
            'taker_id' => $payment->taker_id,
            'updated_at' => substr($payment->updated_at, 0, 10) // Take only the date part
        ]);

        $payments_collection = $payments_collection->groupBy('updated_at');


        $payed = $taken = [];
        $current_date = $from_date->toMutable();
        
        while ($current_date <= $until_date) {
            $date = $current_date->toImmutable();

            $payed[$date->format('Y-m-d')] = ($payments_collection[$date->format('Y-m-d')] ?? null)?->where('payer_id', $user_id)->sum('amount') ?? 0;

            $taken[$date->format('Y-m-d')] = ($payments_collection[$date->format('Y-m-d')] ?? null)?->where('taker_id', $user_id)->sum('amount') ?? 0;
            $current_date->addDay();
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
        $receivers_collection = $group->purchaseReceivers()->join('purchases', 'purchases.id', '=', 'purchase_receivers.purchase_id');
        if(isset($request->category)){
            $purchases_collection->where('category', $request->category);
            $receivers_collection->where('category', $request->category);
        }
        $purchases_collection = $purchases_collection
            ->where('buyer_id', $user_id)
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->select(['amount', 'updated_at'])
            ->get();
        $receivers_collection = $receivers_collection
            ->where('receiver_id', $user_id)
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->select(['purchase_receivers.amount', 'updated_at'])
            ->get();
        
        $purchases_collection = $purchases_collection->map(fn ($purchase) => [
            'amount' => $purchase->amount,
            'updated_at' => substr($purchase->updated_at, 0, 10) // Take only the date part
        ]);

        $receivers_collection = $receivers_collection->map(fn ($receiver) => [
            'amount' => $receiver->amount,
            'updated_at' => substr($receiver->updated_at, 0, 10) // Take only the date part
        ]);

        $purchases_collection = $purchases_collection->groupBy('updated_at');
        $receivers_collection = $receivers_collection->groupBy('updated_at');

        $bought = $received = [];
        $current_date = $from_date->toMutable();
        
        while ($current_date <= $until_date) {
            $date = $current_date->toImmutable();

            $bought[$date->format('Y-m-d')] = ($purchases_collection[$date->format('Y-m-d')] ?? null)?->sum('amount') ?? 0;

            $received[$date->format('Y-m-d')] = ($receivers_collection[$date->format('Y-m-d')] ?? null)?->sum('amount') ?? 0;
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
            'category' => ['nullable', Rule::in($group->categories)]
        ]);
        if ($validator->fails()) abort(400, $validator->errors()->first());

        $from_date  = Carbon::parse($request->from_date)->toImmutable();
        $until_date = Carbon::parse($request->until_date)->toImmutable();

        $purchases_collection = $group->purchases();
        $payments_collection = $group->payments();
        if(isset($request->category)){
            $purchases_collection->where('category', $request->category);
            $payments_collection->where('category', $request->category);
        }
        $purchases_collection = $purchases_collection
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $payments_collection = $payments_collection
            ->whereBetween('updated_at', [
                $from_date->format('Y-m-d'),
                $until_date->addDay()->format('Y-m-d')
            ])
            ->get();

        $purchases_collection = $purchases_collection->map(fn ($purchase) => [
            'amount' => $purchase->amount,
            'updated_at' => substr($purchase->updated_at, 0, 10) // Take only the date part
        ]);

        $payments_collection = $payments_collection->map(fn ($payment) => [
            'amount' => $payment->amount,
            'updated_at' => substr($payment->updated_at, 0, 10) // Take only the date part
        ]);

        $purchases_collection = $purchases_collection->groupBy('updated_at');
        $payments_collection = $payments_collection->groupBy('updated_at');

        $payments = $purchases = [];
        $current_date   = $from_date->toMutable();

        while ($current_date <= $until_date) {
            $date = $current_date->toImmutable();

            $payments[$date->format('Y-m-d')] = ($payments_collection[$date->format('Y-m-d')] ?? null)?->sum('amount') ?? 0;

            $purchases[$date->format('Y-m-d')] = ($purchases_collection[$date->format('Y-m-d')] ?? null)?->sum('amount') ?? 0;
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
