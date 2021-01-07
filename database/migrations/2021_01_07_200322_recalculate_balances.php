<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Transactions\Purchase;

class RecalculateBalances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Purchase::all() as $purchase) {
            $receivers = $purchase->receivers->map(function ($item, $key) {
                return $item->user->id;
            });
            $purchase->receivers()->delete();
            $purchase->createReceivers($receivers->toArray());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
