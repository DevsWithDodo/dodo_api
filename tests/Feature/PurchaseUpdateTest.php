<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Purchase;
use App\Group;
use App\User;

class PurchaseUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected Purchase $purchase;
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
        $purchase_data = [];
        $purchase_data['amount'] = 100;
        $purchase_data['name'] = 'Test purchase';
        $purchase_data['buyer_id'] = $this->users[0]->id;
        $purchase_data['group_id'] = $this->group->id;
        $purchase_data['group_currency'] = $this->group->currency;
        $purchase_data['original_currency'] = 'HUF';
        $purchase_data['receivers'] = [
            ['user_id' => $this->users[1]->id],
            ['user_id' => $this->users[2]->id, 'amount' => 50],
            ['user_id' => $this->users[3]->id],
        ];
        $this->purchase = Purchase::createWithReceivers($purchase_data);
    }

    /**
     * @test
     * Update purchase
     */
    public function updateAmount()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->putJson(route('purchases.update', $this->purchase->id), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'HUF',
                'receivers' => [
                    ['user_id' => $this->users[1]->id],
                    ['user_id' => $this->users[2]->id, 'amount' => 200],
                    ['user_id' => $this->users[3]->id],
                ],
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            300,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -50,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -200,
            $this->group->member($this->users[2]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -50,
            $this->group->member($this->users[3]->id)->member_data->balance,
            0.1
        );

        $this->checkBalanceSum();
    }

    /**
     * @test
     * Update purchase
     */
    public function updateCurrency()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->putJson(route('purchases.update', $this->purchase->id), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'EUR',
                'receivers' => [
                    ['user_id' => $this->users[1]->id],
                    ['user_id' => $this->users[2]->id],
                    ['user_id' => $this->users[3]->id],
                ],
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
     * Update purchase
     */
    public function updateCurrencyAndAmount()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->putJson(route('purchases.update', $this->purchase->id), [
                'name' => 'test purchase',
                'amount' => 200,
                'buyer_id' => $this->users[0]->id,
                'currency' => 'EUR',
                'receivers' => [
                    ['user_id' => $this->users[1]->id],
                    ['user_id' => $this->users[2]->id],
                    ['user_id' => $this->users[3]->id, 'amount' => 100],
                ],
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            200*400,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -50*400,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -50*400,
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
     * Update purchase
     */
    public function updateBuyer()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->putJson(route('purchases.update', $this->purchase->id), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[1]->id,
                'currency' => 'EUR',
                'receivers' => [
                    ['user_id' => $this->users[0]->id],
                    ['user_id' => $this->users[2]->id],
                    ['user_id' => $this->users[3]->id],
                ],
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            -100*400,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            300*400,
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
     * Update purchase
     */
    public function updateEverything()
    {
        $response = $this->actingAs($this->users[0], 'api')
            ->putJson(route('purchases.update', $this->purchase->id), [
                'name' => 'test purchase',
                'amount' => 300,
                'buyer_id' => $this->users[1]->id,
                'currency' => 'EUR',
                'receivers' => [
                    ['user_id' => $this->users[1]->id, 'amount' => 100],
                    ['user_id' => $this->users[2]->id],
                ],
            ]);
        $response->assertStatus(204);

        $this->assertEqualsWithDelta(
            0,
            $this->group->member($this->users[0]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            300*400-100*400,
            $this->group->member($this->users[1]->id)->member_data->balance,
            0.1
        );
        $this->assertEqualsWithDelta(
            -200*400,
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
