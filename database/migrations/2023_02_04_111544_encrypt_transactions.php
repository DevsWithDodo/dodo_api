<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;

class EncryptTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('original_amount')->nullable()->change();
            $table->text('original_currency')->nullable()->change();
            $table->text('amount')->change();
            $table->text('note')->change();
        });

        $dispatcher = Payment::getEventDispatcher();
        Payment::unsetEventDispatcher();
        foreach (Payment::all() as $payment) {
            $payment->original_amount = encrypt($payment->getOriginal('original_amount'));
            $payment->original_currency = encrypt($payment->getOriginal('original_currency'));
            $payment->amount = encrypt($payment->getOriginal('amount'));
            $payment->note = encrypt($payment->getOriginal('note'));
            $payment->save(['timestamps' => false]);
        };
        Payment::setEventDispatcher($dispatcher);

        Schema::table('purchases', function (Blueprint $table) {
            $table->text('original_amount')->nullable()->change();
            $table->text('original_currency')->nullable()->change();
            $table->text('amount')->change();
            $table->text('name')->change();
        });

        $dispatcher = Purchase::getEventDispatcher();
        Purchase::unsetEventDispatcher();
        foreach (Purchase::all() as $purchase) {
            $purchase->original_amount = encrypt($purchase->getOriginal('original_amount'));
            $purchase->original_currency = encrypt($purchase->getOriginal('original_currency'));
            $purchase->amount = encrypt($purchase->getOriginal('amount'));
            $purchase->name = encrypt($purchase->getOriginal('name'));
            $purchase->save(['timestamps' => false]);
        };
        Purchase::setEventDispatcher($dispatcher);

        Schema::table('purchase_receivers', function (Blueprint $table) {
            $table->text('original_amount')->nullable()->change();
            $table->text('amount')->change();
        });

        $dispatcher = PurchaseReceiver::getEventDispatcher();
        PurchaseReceiver::unsetEventDispatcher();
        foreach (PurchaseReceiver::all() as $purchase_receiver) {
            $purchase_receiver->original_amount = encrypt($purchase_receiver->getOriginal('original_amount'));
            $purchase_receiver->amount = encrypt($purchase_receiver->getOriginal('amount'));
            $purchase_receiver->save(['timestamps' => false]);
        };
        PurchaseReceiver::setEventDispatcher($dispatcher);

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Reload database dump
    }
}
