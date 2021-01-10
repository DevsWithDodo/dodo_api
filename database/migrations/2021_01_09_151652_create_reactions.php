<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo "create purchase_reactions table" . "\n";
        Schema::create('purchase_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('purchase_id');
            $table->timestamps();
        });

        echo "create payment_reactions table" . "\n";
        Schema::create('payment_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('payment_id');
            $table->timestamps();
        });

        echo "create request_reactions table" . "\n";
        Schema::create('request_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('request_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions');
    }
}
