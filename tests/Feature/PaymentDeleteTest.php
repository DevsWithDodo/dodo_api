<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Payment;
use App\Group;
use App\User;

class PaymentDeleteTest extends TestCase
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
            $this->group->members()->attach($user->id, ['nickname' => encrypt($user->username), 'balance' => encrypt('0')]);
        }
        $this->payment = Payment::create([
            'group_id' => $this->group->id,
            'payer_id' => $this->users[0]->id,
            'taker_id' => $this->users[1]->id,
            'amount' => encrypt('100'),
            'original_amount' => encrypt('100'),
            'currency' => 'HUF'
        ]);
    }

    /**
     * @test
     * Update payment
     */
    public function deletePayment()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->deleteJson(route('payments.delete', $this->payment->id));
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
            0,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[3]->id)->member_data->balance,
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
