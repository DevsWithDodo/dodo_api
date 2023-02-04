<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Group;
use App\User;

class PurchaseCreateTest extends TestCase
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
            $this->group->members()->attach($user->id, ['nickname' => encrypt($user->username), 'balance' => encrypt('0')]);
        }
    }

    /**
     * @test
     * Create payment
     */
    public function createSimplePurchase()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->postJson(route('purchases.store'), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'HUF',
                'receivers' => [
                    [
                        'user_id' => $this->users[1]->id,
                    ],
                    [
                        'user_id' => $this->users[2]->id,
                    ],
                    [
                        'user_id' => $this->users[3]->id,
                    ],
                ],
                'group' => $this->group->id,
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            300,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[3]->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

     /**
     * @test
     * Create payment
     */
    public function createPurchaseWithDifferentCurrency()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->postJson(route('purchases.store'), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'EUR',
                'receivers' => [
                    [
                        'user_id' => $this->users[1]->id,
                    ],
                    [
                        'user_id' => $this->users[2]->id,
                    ],
                    [
                        'user_id' => $this->users[3]->id,
                    ],
                ],
                'group' => $this->group->id,
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            300*400,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100*400,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100*400,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100*400,
            $this->group->member($this->users[3]->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Create payment
     */
    public function createSimplePurchaseWithBuyerIncluded()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->postJson(route('purchases.store'), [
                'name' => 'test purchase',
                'amount' => 400,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'HUF',
                'receivers' => [
                    [
                        'user_id' => $this->users[0]->id,
                    ],
                    [
                        'user_id' => $this->users[1]->id,
                    ],
                    [
                        'user_id' => $this->users[2]->id,
                    ],
                    [
                        'user_id' => $this->users[3]->id,
                    ],
                ],
                'group' => $this->group->id,
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            300,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -100,
            $this->group->member($this->users[3]->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Create payment
     */
    public function createPurchaseWithUniqueAmounts()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->postJson(route('purchases.store'), [
                'name' => 'test purchase',
                'amount' => 100,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'HUF',
                'receivers' => [
                    [
                        'user_id' => $this->users[1]->id,
                        'amount' => 50,
                    ],
                    [
                        'user_id' => $this->users[2]->id,
                    ],
                    [
                        'user_id' => $this->users[3]->id,
                    ],
                ],
                'group' => $this->group->id,
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            100,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -50,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -25,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );

        $this->assertEqualsWithDelta(
            -25,
            $this->group->member($this->users[3]->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Create payment
     */
    public function createPurchaseWithUniqueAmountsAndDifferentCurrency()
    {

        $response = $this->actingAs($this->users[0], 'api')
            ->postJson(route('purchases.store'), [
                'name' => 'test purchase',
                'group' => $this->group->id,
                'amount' => 100,
                'currency' => 'EUR',
                'receivers' => [
                    ['user_id' => $this->users[0]->id],
                    ['user_id' => $this->users[1]->id, 'amount' => 10],
                    ['user_id' => $this->users[2]->id, 'amount' => 15],
                    ['user_id' => $this->users[3]->id],
                ]
            ]);
        $response->assertStatus(204);


        $this->assertEqualsWithDelta(
            400 * (100 - (100 - 25) / 2),
            $this->group->member($this->users[0]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            400 * -10,
            $this->group->member($this->users[1]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            400 * -15,
            $this->group->member($this->users[2]->id)->member_data->balance,
            1
        );
        $this->assertEqualsWithDelta(
            400 * - (100 - 25) / 2,
            $this->group->member($this->users[3]->id)->member_data->balance,
            1
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
