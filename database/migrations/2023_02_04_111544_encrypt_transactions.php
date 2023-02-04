<?php

use App\Member;
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
            $payment->original_amount = encrypt($payment->getRawOriginal('original_amount'));
            $payment->original_currency = encrypt($payment->getRawOriginal('original_currency'));
            $payment->amount = encrypt($payment->getRawOriginal('amount'));
            $payment->note = encrypt($payment->getRawOriginal('note'));
            $payment->updated_at = DB::raw('updated_at');
            $payment->save();
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
            $purchase->original_amount = encrypt($purchase->getRawOriginal('original_amount'));
            $purchase->original_currency = encrypt($purchase->getRawOriginal('original_currency'));
            $purchase->amount = encrypt($purchase->getRawOriginal('amount'));
            $purchase->name = encrypt($purchase->getRawOriginal('name'));
            $purchase->updated_at = DB::raw('updated_at');
            $purchase->save();
        };
        Purchase::setEventDispatcher($dispatcher);

        Schema::table('purchase_receivers', function (Blueprint $table) {
            $table->text('original_amount')->nullable()->change();
            $table->text('amount')->change();
        });

        $dispatcher = PurchaseReceiver::getEventDispatcher();
        PurchaseReceiver::unsetEventDispatcher();
        foreach (PurchaseReceiver::all() as $purchase_receiver) {
            $purchase_receiver->original_amount = encrypt($purchase_receiver->getRawOriginal('original_amount'));
            $purchase_receiver->amount = encrypt($purchase_receiver->getRawOriginal('amount'));
            $purchase_receiver->updated_at = DB::raw('updated_at');
            $purchase_receiver->save();
        };
        PurchaseReceiver::setEventDispatcher($dispatcher);

        Schema::table('group_user', function (Blueprint $table) {
            $table->text('nickname')->change();
            $table->text('balance')->change();
        });

        foreach (Member::all() as $member) {
            $member->nickname = encrypt($member->getRawOriginal('nickname'));
            $member->balance = encrypt($member->getRawOriginal('balance'));
            $member->updated_at = DB::raw('updated_at');
            $member->save();
        };
        
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
