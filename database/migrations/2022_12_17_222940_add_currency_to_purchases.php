<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('original_amount', 19, 4)->nullable();
            $table->string('original_currency')->nullable();
        });

        Schema::table('purchase_receivers', function (Blueprint $table) {
            $table->decimal('original_amount', 19, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('original_amount');
            $table->dropColumn('original_currency');
        });

        Schema::table('purchase_receivers', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
    }
}
