<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Transactions\Purchase;

class RecalculateTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo "recalculate transactions" . "\n";
        foreach (Purchase::all() as $purchase) {
            $receivers = $purchase->receivers->map(function ($item, $key) {
                return $item->user->id;
            });
            $purchase->receivers()->delete();
            echo "create new receivers for purchase id " . $purchase->id . "\n";
            $purchase->withoutEvents(function () use ($purchase, $receivers) {
                $purchase->createReceivers($receivers->toArray());
            });
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
