<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('anyone_can_invite')->default(true);
            $table->char('currency', 3);
            $table->string('invitation')->unique();
            $table->timestampsTz();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('group_id');
            $table->string('nickname');
            $table->boolean('is_admin')->default(0);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('invitations');
    }
}
