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
            ['id' => 'dominik#0000', 'password' => Hash::make(1234)],
            ['id' => 'samu#0000', 'password' => Hash::make(1234)]
        ]);
        DB::table('groups')->insert([
            ['name' => 'Csocsort'],
            ['name' => 'Sajt']
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => 'dominik#0000',
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => 'dominik#0000',
                'group_id' => '2',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => null,
            ],            
            [
                'user_id' => 'samu#0000',
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
            ['purchase_id' => 1, 'buyer_id' => "dominik#0000", 'amount' => 500.8],
            ['purchase_id' => 2, 'buyer_id' => "samu#0000", 'amount' => 1200.6]
        ]);
        DB::table('receivers')->insert([
            ['purchase_id' => 1, 'receiver_id' => "dominik#0000", 'amount' => 250.6],
            ['purchase_id' => 1, 'receiver_id' => "samu#0000", 'amount' => 250.6],
            ['purchase_id' => 2, 'receiver_id' => "samu#0000", 'amount' => 1200.25],
        ]);

        DB::table('payments')->insert([
            ['group_id' => 1, 'payer_id'=> "dominik#0000", 'taker_id'=> "samu#0000", 'amount' => 500],
            ['group_id' => 1, 'payer_id'=> "samu#0000", 'taker_id'=> "dominik#0000", 'amount' => 100],
            ['group_id' => 1, 'payer_id'=> "samu#0000", 'taker_id'=> "dominik#0000", 'amount' => 300],
        ]);

        App\Http\Controllers\GroupController::refreshBalances(App\Group::find(1));
    }
}
