<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Transactions\Payment;

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
        $payments = App\Transactions\Payment::all();
        foreach ($payments as $payment) {
            $payment->original_amount = encrypt($payment->getOriginal('original_amount'));
            $payment->original_currency = encrypt($payment->getOriginal('original_currency'));
            $payment->amount = encrypt($payment->getOriginal('amount'));
            $payment->note = encrypt($payment->getOriginal('note'));
            $payment->save(['timestamps' => false]);
        };
        Payment::setEventDispatcher($dispatcher);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dispatcher = Payment::getEventDispatcher();
        Payment::unsetEventDispatcher();
        $payments = App\Transactions\Payment::all();
        foreach ($payments as $payment) {
            $payment->original_amount = decrypt($payment->getOriginal('original_amount'));
            $payment->original_currency = decrypt($payment->getOriginal('original_currency'));
            $payment->amount = decrypt($payment->getOriginal('amount'));
            $payment->note = decrypt($payment->getOriginal('note'));
            $payment->save(['timestamps' => false]);
        };
        Payment::setEventDispatcher($dispatcher);
    }
}
