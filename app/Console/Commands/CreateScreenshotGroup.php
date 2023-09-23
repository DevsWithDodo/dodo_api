<?php

namespace App\Console\Commands;

use App\Group;
use App\Member;
use App\Request;
use App\Transactions\Purchase;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateScreenshotGroup extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'group:screenshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the group which can be used for creating screenshots';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $samu = User::firstWhere('username', 'samu');
        if ($samu == null) {
            $samu = User::create([
                'username' => 'samu',
                'password' => Hash::make('1234'),
                'default_currency' => 'USD',
                'language' => 'en',
            ]);
        }
        $group = Group::create([
            'name' => 'Italian trip 🌴',
            'currency' => 'EUR',
            'invitation' => Str::random(20),
            'admin_approval' => false,
        ]);

        $group->members()->attach($samu->id, [
            'nickname' => encrypt('Sam😎'),
            'balance' => encrypt('0'),
            'is_admin' => true,
        ]);

        $saci = User::create([
            'username' => null,
            'password' => null,
            'password_reminder' => null,
            'default_currency' => $group->currency,
            'fcm_token' => null,
            'language' => 'en',
        ]);
        $group->members()->attach($saci->id, [
            'nickname' => encrypt('Sarah🐶'),
            'balance' => encrypt("0"),
            'is_admin' => false,
        ]);

        $dominik = User::create([
            'username' => null,
            'password' => null,
            'password_reminder' => null,
            'default_currency' => $group->currency,
            'fcm_token' => null,
            'language' => 'en',
        ]);
        $group->members()->attach($dominik->id, [
            'nickname' => encrypt('Dominique🖥️'),
            'balance' => encrypt("0"),
            'is_admin' => false,
        ]);

        $lili = User::create([
            'username' => null,
            'password' => null,
            'password_reminder' => null,
            'default_currency' => $group->currency,
            'fcm_token' => null,
            'language' => 'en',
        ]);
        $group->members()->attach($lili->id, [
            'nickname' => encrypt('Lilly🌸'),
            'balance' => encrypt("0"),
            'is_admin' => false,
        ]);

        $date = Carbon::now()->subMonths(2);

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Flight to Rome✈️',
            'category' => 'transport',
            'amount' => '212',
            'buyer_id' => $saci->id,
            'receivers' => $group->members->map(fn (User $member) => [
                'user_id' => $member->id,
            ])->toArray(),
            'group_currency' => $group->currency,
            'original_currency' => 'USD',
        ]);
        $this->updateDate($purchase, $date);

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Flight back from Rome✈️',
            'category' => 'transport',
            'amount' => '298',
            'buyer_id' => $saci->id,
            'receivers' => $group->members->map(fn (User $member) => [
                'user_id' => $member->id,
            ])->toArray(),
            'group_currency' => $group->currency,
            'original_currency' => 'USD',
        ]);
        $this->updateDate($purchase, $date);

        $date->addWeeks(2);

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Subway tickets',
            'category' => 'transport',
            'amount' => '80',
            'buyer_id' => $dominik->id,
            'receivers' => $group->members->map(fn (User $member) => [
                'user_id' => $member->id,
            ])->toArray(),
            'group_currency' => $group->currency,
            'original_currency' => 'EUR',
        ]);
        $this->updateDate($purchase, $date);

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Pizza for all 🍕',
            'category' => 'food',
            'amount' => '60',
            'buyer_id' => $dominik->id,
            'receivers' => [
                0 => [
                    'user_id' => $samu->id,
                    'amount' => 10,
                ],
                1 => [
                    'user_id' => $dominik->id,
                    'amount' => 12,
                ],
                2 => [
                    'user_id' => $saci->id,
                    'amount' => 16,
                ],
                3 => [
                    'user_id' => $lili->id,
                ]
            ],
            'group_currency' => $group->currency,
            'original_currency' => 'EUR',
        ]);
        $this->updateDate($purchase, $date);

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Sandwiches for Florence',
            'category' => 'groceries',
            'amount' => '50',
            'buyer_id' => $lili->id,
            'receivers' => [
                0 => [
                    'user_id' => $samu->id,
                    'amount' => 13.6,
                ],
                1 => [
                    'user_id' => $dominik->id,
                    'amount' => 11.43,
                ],
                2 => [
                    'user_id' => $saci->id,
                    'amount' => 13.56,
                ],
                3 => [
                    'user_id' => $lili->id,
                ]
            ],
            'group_currency' => $group->currency,
            'original_currency' => 'EUR',
        ]);
        $this->updateDate($purchase, $date);

        $date->addDay();

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Train to Florence and back🚂',
            'category' => 'transport',
            'amount' => '120',
            'buyer_id' => $samu->id,
            'receivers' => $group->members->map(fn (User $member) => [
                'user_id' => $member->id,
            ])->toArray(),
            'group_currency' => $group->currency,
            'original_currency' => 'EUR',
        ]);
        $this->updateDate($purchase, $date);

        $date->addDay();

        $purchase = Purchase::createWithReceivers([
            'group_id' => $group->id,
            'name' => 'Lunch day 2 🍝',
            'category' => 'food',
            'amount' => '65',
            'buyer_id' => $dominik->id,
            'receivers' => [
                0 => [
                    'user_id' => $samu->id,
                    'amount' => 12.5,
                ],
                1 => [
                    'user_id' => $dominik->id,
                    'amount' => 14.5,
                ],
                2 => [
                    'user_id' => $saci->id,
                    'amount' => 13.5,
                ],
                3 => [
                    'user_id' => $lili->id,
                ]
            ],
            'group_currency' => $group->currency,
            'original_currency' => 'EUR',
        ]);
        $this->updateDate($purchase, $date);

        Request::create([
            'name' => 'Sunscreen',
            'group_id' => $group->id,
            'requester_id' => $samu->id,
        ]);

        Request::create([
            'name' => 'Hats👒',
            'group_id' => $group->id,
            'requester_id' => $saci->id,
        ]);



        return Command::SUCCESS;
    }

    private function updateDate(Model $model, Carbon $date) {
        $model->updated_at = $date;
        $model->save();
    }
}
