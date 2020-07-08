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
            ['id' => 'Dominik#5342', 'password' => Hash::make(1234), 'email' => null],
            ['id' => 'Samu#1252', 'password' => Hash::make(1234), 'email' => 'samu@csocsort.com']
        ]);
        DB::table('groups')->insert([
            ['name' => 'Csocsort'],
            ['name' => 'Sajt']
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => 'Dominik#5342',
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => 'Dominik#5342',
                'group_id' => '2',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => null,
            ],            
            [
                'user_id' => 'Samu#1252',
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
            ['purchase_id' => 1, 'buyer_id' => "Dominik#5342", 'amount' => 500],
            ['purchase_id' => 2, 'buyer_id' => "Samu#1252", 'amount' => 1200]
        ]);
        DB::table('receivers')->insert([
            ['purchase_id' => 1, 'receiver_id' => "Dominik#5342", 'amount' => 250],
            ['purchase_id' => 1, 'receiver_id' => "Samu#1252", 'amount' => 250],
            ['purchase_id' => 2, 'receiver_id' => "Dominik#5342", 'amount' => 1200],
        ]);

        DB::table('payments')->insert([
            ['group_id' => 1, 'payer_id'=> "Dominik#5342", 'taker_id'=> "Samu#1252", 'amount' => 500],
            ['group_id' => 1, 'payer_id'=> "Samu#1252", 'taker_id'=> "Dominik#5342", 'amount' => 100],
            ['group_id' => 1, 'payer_id'=> "Samu#1252", 'taker_id'=> "Dominik#5342", 'amount' => 300],
        ]);

        App\Http\Controllers\GroupController::refreshBalances(App\Group::find(1));
    }
}
