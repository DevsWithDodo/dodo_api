<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Payment;
use Illuminate\Support\Facades\Artisan;
use App\Group;
use App\User;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function buyerIncluded()
    {
        for ($i = 0; $i < 5; $i++) {
            Artisan::call('migrate');
            $group = Group::factory()->create();
            $users = User::factory()->count(rand(3, 20))->create();
            foreach ($users as $user) {
                $group->members()->attach($user->id, ['nickname' => $user->username]);
            }
            $payer = $group->members->random();
            $taker = $group->members->except($payer->id)->random();
            $payment = Payment::factory()->make();
            $response = $this->actingAs($payer, 'api')
                ->postJson(route('payments.store'), [
                    'note' => $payment->note,
                    'group' => $group->id,
                    'taker_id' => $taker->id,
                    'amount' => $payment->amount
                ]);
            $response->assertStatus(201);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance, $member->member_data->balance);
            }
            $this->assertTrue(
                abs(($payment->amount)
                    - $group->member($payer->id)->member_data->balance) < 0.01
            );
            $this->assertTrue(
                abs(0 - ($payment->amount)
                    - $group->member($taker->id)->member_data->balance) < 0.01
            );
            $this->assertTrue(0 == $balance);
        }
    }

    /**
     * @test
     * Update payment
     */
    public function Update()
    {
        for ($i = 0; $i < 5; $i++) {
            Artisan::call('migrate');
            $group = Group::factory()->create();
            $users = User::factory()->count(rand(3, 20))->create();
            foreach ($users as $user) {
                $group->members()->attach($user->id, ['nickname' => $user->username]);
            }
            $payer = $group->members->random();
            $taker = $group->members->except($payer->id)->random();
            $taker_2 = $group->members->except($payer->id)->random();
            $payment = Payment::factory()->make();
            $payment_2 = Payment::factory()->make();
            $response = $this->actingAs($payer, 'api')
                ->postJson(route('payments.store'), [
                    'note' => $payment->note,
                    'group' => $group->id,
                    'taker_id' => $taker->id,
                    'amount' => $payment->amount
                ]);
            $response->assertStatus(201);

            $id = $response->json()["payment_id"];
            $response = $this->actingAs($payer, 'api')
                ->putJson(route('payments.update', $id),  [
                    'note' => $payment_2->name,
                    'taker_id' => $taker_2->id,
                    'amount' => $payment_2->amount
                ]);
            $response->assertStatus(200);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance, $member->member_data->balance);
            }
            $this->assertTrue(
                bcsub(($payment_2->amount),
                    $group->member($payer->id)->member_data->balance
                ) == 0
            );
            $this->assertTrue(
                bcsub(
                    (-1) * $payment_2->amount,
                    $group->member($taker_2->id)->member_data->balance
                ) == 0
            );
            if ($taker->id != $taker_2->id)
                $this->assertTrue(0 == $group->member($taker->id)->member_data->balance);
            $this->assertTrue(0 == $balance);
        }
    }
}
