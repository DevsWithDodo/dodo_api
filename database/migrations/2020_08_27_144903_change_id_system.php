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

        DB::table('users')->update(['api_token' => null]);

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

        //payments table
        foreach(DB::table('payments')->get() as $row) { 
            DB::table('payments')->where('payer_id', $row->payer_id)->update(['payer_id' => User::firstWhere('username', $row->payer_id)->id]);
            DB::table('payments')->where('taker_id', $row->taker_id)->update(['taker_id' => User::firstWhere('username', $row->taker_id)->id]);
        }
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('taker_id')->change();
            $table->integer('payer_id')->change();
        });

        //requests table
        foreach(DB::table('requests')->get() as $row) {
            DB::table('requests')->where('requester_id', $row->requester_id)->update(['requester_id' => User::firstWhere('username', $row->requester_id)->id]);
            if($row->fulfiller_id){
                DB::table('requests')->where('fulfiller_id', $row->fulfiller_id)->update(['fulfiller_id' => User::firstWhere('username', $row->fulfiller_id)->id]);
            }
        }
        Schema::table('requests', function (Blueprint $table) {
            $table->integer('fulfiller_id')->change();
            $table->integer('requester_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->renameColumn('username', 'id');
            $table->string('id')->primary()->change(); 
        });

        //TODO: other
    }
}
