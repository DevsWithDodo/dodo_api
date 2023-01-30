<?php

use App\Group;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PolymorphicReactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Group::class, 'group_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactionable');
            $table->timestamps();
        });

        DB::cursor('SELECT * FROM purchase_reactions', function ($row) {
            DB::table('reactions')->insert([
                'reaction' => $row->reaction,
                'user_id' => $row->user_id,
                'group_id' => $row->group_id,
                'reactionable_id' => $row->purchase_id,
                'reactionable_type' => 'App\Transactions\Purchase',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        });

        DB::cursor('SELECT * FROM payment_reactions', function ($row) {
            DB::table('reactions')->insert([
                'reaction' => $row->reaction,
                'user_id' => $row->user_id,
                'group_id' => $row->group_id,
                'reactionable_id' => $row->payment_id,
                'reactionable_type' => 'App\Transactions\Payment',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        });

        DB::cursor('SELECT * FROM request_reactions', function ($row) {
            DB::table('reactions')->insert([
                'reaction' => $row->reaction,
                'user_id' => $row->user_id,
                'group_id' => $row->group_id,
                'reactionable_id' => $row->request_id,
                'reactionable_type' => 'App\Request',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        });

        Schema::dropIfExists('purchase_reactions');
        Schema::dropIfExists('payment_reactions');
        Schema::dropIfExists('request_reactions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions');
        Schema::create('purchase_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('purchase_id');
            $table->integer('group_id');
            $table->timestamps();
        });
        Schema::create('payment_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('payment_id');
            $table->integer('group_id');
            $table->timestamps();
        });
        Schema::create('request_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reaction');
            $table->integer('user_id');
            $table->integer('request_id');
            $table->integer('group_id');
            $table->timestamps();
        });
    }
}
