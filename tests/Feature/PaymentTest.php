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
            $response->assertStatus(204);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance, $member->member_data->balance);
            }
            $this->assertEqualsWithDelta($payment->amount, $group->member($payer->id)->member_data->balance, 0.01);
            $this->assertEqualsWithDelta(0 - ($payment->amount), $group->member($taker->id)->member_data->balance, 0.01);
            $this->assertEquals(0, $balance);
        }
    }

    /**
     * @test
     * Update payment
     */
    public function updateAmount()
    {
        $group = Group::factory()->create(['currency' => 'HUF']);
        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $users[0];
        $taker = $users[1];
        $payment = Payment::create([
            'group_id' => $group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $group->member($payer->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            -150,
            $group->member($taker->id)->member_data->balance,
            1
        );


        $balance = 0;
        foreach ($group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);

    }

    /**
     * @test
     * Update payment
     */
    public function updatePayer()
    {
        $group = Group::factory()->create(['currency' => 'HUF']);
        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $users[0];
        $taker = $users[1];
        $payment = Payment::create([
            'group_id' => $group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $payer = $users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            0,
            $group->member($users[0]->id)->member_data->balance,
            1
        );

        $this->assertEqualsWithDelta(
            150,
            $group->member($payer->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            -150,
            $group->member($taker->id)->member_data->balance,
            1
        );

        $balance = 0;
        foreach ($group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);

    }

    /**
     * @test
     * Update payment
     */
    public function updateTaker()
    {
        $group = Group::factory()->create(['currency' => 'HUF']);
        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $users[0];
        $taker = $users[1];
        $payment = Payment::create([
            'group_id' => $group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $taker = $users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $group->member($payer->id)->member_data->balance,
            1
        );

        $this->assertEqualsWithDelta(
            0,
            $group->member($users[1]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            -150,
            $group->member($taker->id)->member_data->balance,
            1
        );

        $balance = 0;
        foreach ($group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);
    }

    /**
     * @test
     * Update payment
     */
    public function updateParticipants()
    {
        $group = Group::factory()->create(['currency' => 'HUF']);
        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $users[0];
        $taker = $users[1];
        $payment = Payment::create([
            'group_id' => $group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $payer = $users[2];
        $taker = $users[3];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $group->member($payer->id)->member_data->balance,
            1
        );

        $this->assertEqualsWithDelta(
            0,
            $group->member($users[0]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            0,
            $group->member($users[1]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            -150,
            $group->member($taker->id)->member_data->balance,
            1
        );

        $balance = 0;
        foreach ($group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);
    }

    /**
     * @test
     * Update payment
     */
    public function switchParticipants()
    {
        $group = Group::factory()->create(['currency' => 'HUF']);
        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $users[0];
        $taker = $users[1];
        $payment = Payment::create([
            'group_id' => $group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $payer = $users[1];
        $taker = $users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $group->member($payer->id)->member_data->balance,
            1
        );

        $this->assertEqualsWithDelta(
            -150,
            $group->member($taker->id)->member_data->balance,
            1
        );

        $balance = 0;
        foreach ($group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);
    }
}
