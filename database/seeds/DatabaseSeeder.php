<?php

use Illuminate\Database\Seeder;
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
        DB::table('users')->insert([
            ['name' => 'Dominik','password' => Hash::make(1234), 'email' => 'dominik@csocsort.com'],
            ['name' => 'Samu','password' => Hash::make(1234), 'email' => 'samu@csocsort.com']
        ]);
        DB::table('groups')->insert([
            ['name' => 'Csocsort'],
            ['name' => 'Sajt']
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => '1',
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => '1',
                'group_id' => '2',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => null,
            ],            
            [
                'user_id' => '2',
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => '1',
                'nickname' => 'Samupipoke'
            ],            
        ]);
        DB::table('purchases')->insert([
            ['name' => 'Sajt','group_id' => 1],
            ['name' => 'Sok Sajt', 'group_id' => 1]
        ]);
        DB::table('buyers')->insert([
            ['purchase_id' => 1, 'buyer_id' => 1, 'amount' => 500],
            ['purchase_id' => 2, 'buyer_id' => 2, 'amount' => 1200]
        ]);
        DB::table('receivers')->insert([
            ['purchase_id' => 1, 'receiver_id' => 1, 'amount' => 250],
            ['purchase_id' => 1, 'receiver_id' => 2, 'amount' => 250],
            ['purchase_id' => 2, 'receiver_id' => 1, 'amount' => 1200],
        ]);

        DB::table('payments')->insert([
            ['group_id' => 1, 'payer_id'=> 1, 'taker_id'=> 2, 'amount' => 500],
            ['group_id' => 1, 'payer_id'=> 2, 'taker_id'=> 1, 'amount' => 100],
            ['group_id' => 1, 'payer_id'=> 2, 'taker_id'=> 1, 'amount' => 300],
        ]);

        App\Http\Controllers\GroupController::refreshBalances(App\Group::find(1));
    }
}
