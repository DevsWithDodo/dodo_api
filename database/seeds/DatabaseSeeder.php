<?php

use Illuminate\Database\Seeder;

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
            ['name' => 'Dominik','password' => 1234],
            ['name' => 'Samu','password' => 1234]
        ]);
        DB::table('groups')->insert([
            ['name' => 'Csocsort'],
            ['name' => 'Sajt']
        ]);
        DB::table('group_user')->insert([
            [
                'user_id' => '1',
                'group_id' => '1',
                'balance' => '5000',
                'is_admin' => 0,
                'nickname' => 'Dominyik',
            ],
            [
                'user_id' => '1',
                'group_id' => '2',
                'balance' => '-200',
                'is_admin' => 0,
                'nickname' => null,
            ],            
            [
                'user_id' => '2',
                'group_id' => '1',
                'balance' => '50',
                'is_admin' => '1',
                'nickname' => 'Samupipoke'
            ],            
        ]);
        
    }
}
