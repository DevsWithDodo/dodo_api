<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Group;
use App\User;
use App\Transactions\Payment;
use App\Transactions\Purchase;
use App\Transactions\PurchaseReceiver;
use App\Request;

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
            'name' => 'Csocsort'
        ]);
        $other_group = Group::factory()->create();
        $dominik = User::factory()->create([
            'username' => 'dominik'
        ]);
        $samu = User::factory()->create([
            'username' => 'samu'
        ]);
        foreach ([$csocsort, $other_group] as $group) {
            $group->members()->attach($dominik->id, [
                'nickname' => $dominik->username, 'is_admin' => true
            ]);
            $group->members()->attach($samu->id, [
                'nickname' => $samu->username, 'is_admin' => true
            ]);

            $users = User::factory()->count(5)->create();
            $users->each(function ($user) use ($group) {
                $group->members()->attach($user->id, [
                    'nickname' => $user->username
                ]);
            });
        }
        $users = $users->concat([$samu, $dominik]);
        $users->each(function ($user) use ($csocsort, $users) {
            //purchase
            Purchase::factory()->count(rand(3, 10))
                ->create([
                    'group_id' => $csocsort->id,
                    'buyer_id' => $user->id
                ])
                ->each(function ($purchase) use ($users) {
                    $count = rand(1, 3);
                    $receivers = $users->random($count);
                    $receivers->each(function ($receiver) use ($purchase, $count) {
                        PurchaseReceiver::factory()
                            ->create([
                                'purchase_id' => $purchase->id,
                                'amount' => $purchase->amount / $count,
                                'receiver_id' => $receiver->id
                            ]);
                    });
                });
            //payment
            Payment::factory()->count(rand(3, 10))
                ->create([
                    'group_id' => $csocsort->id,
                    'taker_id' => $user->id,
                    'payer_id' => $users->except($user->id)->random()->id
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
