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
        $csocsort = factory(App\Group::class)->create([
            'name' => 'Csocsort'
        ]);
        $other_group = factory(App\Group::class)->create();
        $dominik = factory(App\User::class)->create([
            'username' => 'dominik'
        ]);
        $samu = factory(App\User::class)->create([
            'username' => 'samu'
        ]);

        foreach ([$csocsort, $other_group] as $group) {
            $group->members()->attach($dominik->id, [
                'nickname' => $dominik->username, 'is_admin' => true
            ]);
            $group->members()->attach($samu->id, [
                'nickname' => $samu->username, 'is_admin' => true
            ]);

            $users = collect([$samu, $dominik])->concat(factory(App\User::class, 5)
                ->create()
                ->each(function ($user) use ($group) {
                    $group->members()->attach($user->id, [
                        'nickname' => $user->username
                    ]);
                }));
            $users->each(function ($user) use ($group, $users) {
                //purchase
                factory(App\Transactions\Purchase::class, rand(3, 10))
                    ->create([
                        'group_id' => $group->id,
                        'buyer_id' => $user->id
                    ])
                    ->each(function ($purchase) use ($group, $users) {
                        $count = rand(1, 3);
                        $receivers = $users->random($count);
                        $receivers->each(function ($receiver) use ($purchase, $count) {
                            factory(App\Transactions\PurchaseReceiver::class)
                                ->create([
                                    'purchase_id' => $purchase->id,
                                    'amount' => $purchase->amount / $count,
                                    'receiver_id' => $receiver->id
                                ]);
                        });
                    });
                //payment
                factory(App\Transactions\Payment::class, rand(3, 10))
                    ->create([
                        'group_id' => $group->id,
                        'taker_id' => $user->id,
                        'payer_id' => $users->except($user->id)->random()->id
                    ]);
                //request
                factory(App\Request::class, rand(2, 4))
                    ->create([
                        'group_id' => $group->id,
                        'requester_id' => $user->id
                    ]);
            });
        };
    }
}
