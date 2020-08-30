<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\User;

class ChangeIdSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->renameColumn('id', 'username');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->id()->first();
            $table->string('username')->unique()->change();
        });

        //group_users table
        foreach(DB::table('group_user')->get() as $row) { 
            DB::table('group_user')->where('user_id', $row->user_id)->update(['user_id' => User::firstWhere('username', $row->user_id)->id]);
        }
        Schema::table('group_user', function (Blueprint $table) {
            $table->integer('user_id')->change();
        });

        //buyers table
        foreach(DB::table('buyers')->get() as $row) { 
            DB::table('buyers')->where('buyer_id', $row->buyer_id)->update(['buyer_id' => User::firstWhere('username', $row->buyer_id)->id]);
        }
        Schema::table('buyers', function (Blueprint $table) {
            $table->integer('buyer_id')->change();
        });

        //receivers table
        foreach(DB::table('receivers')->get() as $row) { 
            DB::table('receivers')->where('receiver_id', $row->receiver_id)->update(['receiver_id' => User::firstWhere('username', $row->receiver_id)->id]);
        }
        Schema::table('receivers', function (Blueprint $table) {
            $table->integer('receiver_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
/*         Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->renameColumn('username', 'id');
            $table->string('id')->primary()->change(); 
        }); */
    }
}
