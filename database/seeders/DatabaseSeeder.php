<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Group;
use App\User;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\Request;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csocsort = Group::factory()->create([
            'name' => 'Csocsort',
            'boosted' => 1,
            'admin_approval' => 1,
            'currency' => 'HUF'
        ]);
        $other_group = Group::factory()->create([
            'currency' => 'EUR'
        ]);
        $dominik = User::factory()->create([
            'username' => 'dominik',
            'password' => Hash::make('1234'),
            'ad_free' => 1,
            'trial' => 0,
        ]);
        $samu = User::factory()->create([
            'username' => 'samu',
            'password' => Hash::make('1234'),
            'trial' => 0
        ]);
        foreach ([$csocsort, $other_group] as $group) {
            $group->members()->attach($dominik->id, [
                'nickname' => encrypt($dominik->username), 'is_admin' => true,
                'balance' => encrypt('0')
            ]);
            $group->members()->attach($samu->id, [
                'nickname' => encrypt($samu->username), 'is_admin' => true,
                'balance' => encrypt('0')
            ]);

            $users = User::factory()->count(5)->create();
            $users->each(function ($user) use ($group) {
                $group->members()->attach($user->id, [
                    'nickname' => encrypt($user->username),
                    'balance' => encrypt('0')
                ]);
                $user->generateToken();
            });
        }
        $users = $csocsort->members;
        $users->each(function ($user) use ($csocsort) {
            //purchase
            $purchases = Purchase::factory()->count(rand(3, 10))
                ->create([
                    'group_id' => $csocsort->id,
                    'buyer_id' => $user->id
                ]);
            //payment
            Payment::factory()->count(rand(3, 10))
                ->create([
                    'group_id' => $csocsort->id,
                    'taker_id' => $user->id,
                    'payer_id' => $csocsort->members->except($user->id)->random()->id
                ]);
            //request
            Request::factory()->count(rand(2, 4))
                ->create([
                    'group_id' => $csocsort->id,
                    'requester_id' => $user->id
                ]);
        });
    }
}
