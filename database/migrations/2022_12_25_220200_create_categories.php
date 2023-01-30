<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('category')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('category')->nullable();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->json('custom_categories')->after('currency')->nullable();
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
            $table->dropColumn('category');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('custom_categories');
        });
    }
}
