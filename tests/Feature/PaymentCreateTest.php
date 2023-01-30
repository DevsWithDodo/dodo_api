<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\TestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Payment;
use Illuminate\Support\Facades\Artisan;
use App\Group;
use App\User;

class PaymentCreateTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /**
     * @test
     * Create payment
     */
    public function create()
    {
        $payer = $this->users[0];
        $taker = $this->users[1];
        $response = $this->actingAs($payer, 'api')
            ->postJson(route('payments.store'), [
                'group' => $this->group->id,
                'taker_id' => $taker->id,
                'payer_id' => $payer->id,
                'amount' => 100,
                'currency' => 'HUF',
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            100,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
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

    /**
     * @test
     * Create payment in different currency
     */
    public function createWithCurrency()
    {
        $payer = $this->users[0];
        $taker = $this->users[1];
        $response = $this->actingAs($payer, 'api')
            ->postJson(route('payments.store'), [
                'group' => $this->group->id,
                'taker_id' => $taker->id,
                'payer_id' => $payer->id,
                'amount' => 4,
                'currency' => 'EUR',
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            4*400,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -4*400,
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
