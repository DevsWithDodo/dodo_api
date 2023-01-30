<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected Payment $payment;
    protected Group $group;
    protected $users;

    public function setUp(): void
    {
        parent::setUp();
        $this->group = Group::factory()->create(['currency' => 'HUF']);
        $this->users = User::factory()->count(4)->create();
        foreach ($this->users as $user) {
            $this->group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $this->payment = Payment::create([
            'group_id' => $this->group->id,
            'payer_id' => $this->users[0]->id,
            'taker_id' => $this->users[1]->id,
            'amount' => 100,
            'original_amount' => 100,
            'currency' => 'HUF'
        ]);
    }

    /**
     * @test
     * Update payment
     */
    public function updateAmount()
    {
        $payer = $this->users[0];
        $taker = $this->users[1];
        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -150,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update payment
     */
    public function updateCurrency()
    {
        $payer = $this->users[0];
        $taker = $this->users[1];
        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 100,
                'currency' => 'EUR'
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            100*400,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -100*400,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

        /**
     * @test
     * Update payment
     */
    public function updateCurrencyAndAmount()
    {
        $payer = $this->users[0];
        $taker = $this->users[1];
        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 5,
                'currency' => 'EUR'
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            5*400,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -5*400,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update payment
     */
    public function updatePayer()
    {
        $this->group = Group::factory()->create(['currency' => 'HUF']);
        $this->users = User::factory()->count(4)->create();
        foreach ($this->users as $user) {
            $this->group->members()->attach($user->id, ['nickname' => $user->username]);
        }
        $payer = $this->users[0];
        $taker = $this->users[1];
        $this->payment = Payment::create([
            'group_id' => $this->group->id,
            'payer_id' => $payer->id,
            'taker_id' => $taker->id,
            'amount' => 100,
        ]);

        $payer = $this->users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            150,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -150,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update payment
     */
    public function updateTaker()
    {
        $payer = $this->users[0];
        $taker = $this->users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -150,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update payment
     */
    public function updateParticipants()
    {

        $payer = $this->users[2];
        $taker = $this->users[3];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -150,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update payment
     */
    public function switchParticipants()
    {

        $payer = $this->users[1];
        $taker = $this->users[2];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 150
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            150,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -150,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );
    }

        /**
     * @test
     * Update payment
     */
    public function updateEverything()
    {

        $payer = $this->users[2];
        $taker = $this->users[3];

        $response = $this->actingAs($payer, 'api')
            ->putJson(route('payments.update', $this->payment->id), [
                'payer_id' => $payer->id,
                'taker_id' => $taker->id,
                'amount' => 15,
                'currency' => 'EUR'
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            15*400,
            $this->group->member($payer->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -15*400,
            $this->group->member($taker->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    private function checkBalanceSum()
    {
        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);
    }
}
