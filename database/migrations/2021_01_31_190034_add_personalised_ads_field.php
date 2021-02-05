<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonalisedAdsField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('personalised_ads')->default(0);
        });

        Schema::table('groups', function(Blueprint $table) {
            $table->renameColumn('anyone_can_invite', 'admin_approval');
        });
        Schema::table('groups', function(Blueprint $table) {
            $table->boolean('admin_approval')->default(0)->change();
        });
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
