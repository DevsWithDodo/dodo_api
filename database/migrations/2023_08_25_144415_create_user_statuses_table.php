<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->dateTimeTz('pin_verified_at');
            $table->integer('pin_verification_count')->default(0);
            $table->enum('trial_status', ['trial', 'expired', 'seen'])->default('trial');
            $table->timestamps();
        });

        foreach(User::all() as $user) {
            $user->status()->create([
                'pin_verified_at' => now(),
                'pin_verification_count' => 0,
                'trial_status' => $user->trial ? 'trial' : 'seen',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_statuses');
    }
}
