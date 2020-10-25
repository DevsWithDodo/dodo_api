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
            ['name' => 'Csocsort', 'currency' => 'CML', 'invitation' => Str::random(20)],
            ['name' => 'Sajt', 'currency' => 'EUR', 'invitation' => Str::random(20)]
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => 1,
                'group_id' => '1',
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => 1,
                'group_id' => '2',
                'is_admin' => 0,
                'nickname' => 'domi',
            ],            
            [
                'user_id' => 2,
                'group_id' => '1',
                'is_admin' => '1',
                'nickname' => 'samuuuu'
            ],            
        ]);
        DB::table('purchases')->insert([
            ['name' => 'Sajt','group_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'buyer_id' => 1, 'amount' => 500],
            ['name' => 'Sok Sajt', 'group_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'buyer_id' => 2, 'amount' => 1200]
        ]);
        DB::table('purchase_receivers')->insert([
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
    }
}
