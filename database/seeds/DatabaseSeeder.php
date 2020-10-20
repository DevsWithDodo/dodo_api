<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            ['username' => 'dominik', 'password' => Hash::make(1234), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'default_currency' => 'CML'],
            ['username' => 'samu', 'password' => Hash::make(1234), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'default_currency' => 'EUR']
        ]);
        DB::table('groups')->insert([
            ['name' => 'Csocsort', 'currency' => 'CML'],
            ['name' => 'Sajt', 'currency' => 'EUR']
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => 1,
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => 1,
                'group_id' => '2',
                'balance' => 5,
                'is_admin' => 0,
                'nickname' => 'domi',
            ],            
            [
                'user_id' => 2,
                'group_id' => '1',
                'balance' => 0,
                'is_admin' => '1',
                'nickname' => 'samuuuu'
            ],            
        ]);
        DB::table('purchases')->insert([
            ['name' => 'Sajt','group_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sok Sajt', 'group_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
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
            ['group_id' => 1, 'payer_id'=> 1, 'taker_id'=> 2, 'amount' => 500, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['group_id' => 1, 'payer_id'=> 2, 'taker_id'=> 1, 'amount' => 100, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['group_id' => 1, 'payer_id'=> 2, 'taker_id'=> 1, 'amount' => 300, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        DB::table('requests')->insert([
            ['name' => '1 kilo trapista', 'group_id' => 1, 'requester_id' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ]);

        DB::table('invitations')->insert([
            ['group_id' => 1, 'token' => Str::random(20), 'usable_once_only' => false]
        ]);
        
        App\Group::find(1)->refreshBalances();
    }
}
