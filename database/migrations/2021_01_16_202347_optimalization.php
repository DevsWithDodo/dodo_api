<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Transactions\PurchaseReceiver;
use App\Transactions\Reactions\PaymentReaction;
use App\Transactions\Reactions\PurchaseReaction;
use App\Transactions\Reactions\RequestReaction;

class Optimalization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchase_receivers', 'group_id')) {
            Schema::table('purchase_receivers', function (Blueprint $table) {
                $table->integer('group_id');
            });

            Schema::table('purchase_reactions', function (Blueprint $table) {
                $table->integer('group_id');
            });

            Schema::table('payment_reactions', function (Blueprint $table) {
                $table->integer('group_id');
            });

            Schema::table('request_reactions', function (Blueprint $table) {
                $table->integer('group_id');
            });
        }

        foreach (PurchaseReceiver::all() as $receiver) {
            $receiver->update(['group_id' => $receiver->purchase->group_id]);
        }
        foreach (PaymentReaction::all() as $reaction) {
            $reaction->update(['group_id' => $reaction->payment->group_id]);
        }
        foreach (PurchaseReceiver::all() as $reaction) {
            $reaction->update(['group_id' => $reaction->purchase->group_id]);
        }
        foreach (RequestReaction::all() as $reaction) {
            $reaction->update(['group_id' => $reaction->request->group_id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_receivers', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });

        Schema::table('purchase_reactions', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });

        Schema::table('payment_reactions', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });

        Schema::table('request_reactions', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
}
