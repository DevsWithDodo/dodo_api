<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('group_id');
            $table->integer('buyer_id');
            $table->decimal('amount', 19, 4);
            $table->timestampsTz();
        });

        Schema::create('purchase_receivers', function (Blueprint $table) {
            $table->id();
            $table->integer('purchase_id');
            $table->integer('receiver_id');
            $table->decimal('amount', 19, 4);
            $table->integer('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('receivers');
    }
}
