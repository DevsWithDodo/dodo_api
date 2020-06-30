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
            $table->timestampsTz();
        });

        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->integer('buyer_id');
            $table->decimal('amount', 19, 4);
        });

        Schema::create('receivers', function (Blueprint $table) {
            $table->id();
            $table->integer('receiver_id');
            $table->decimal('amount', 19, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
