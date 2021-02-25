<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Transactions\Purchase;
use App\Group;
use App\User;
use Illuminate\Support\Facades\Hash;

class MemberTest extends TestCase
{
    use RefreshDatabase;
    protected Group $group;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
        $this->group = Group::factory()->create();
        $users = User::factory()->count(rand(3, 20))->create(['password' => Hash::make('1234')]); //not guests
        foreach ($users as $user) {
            $this->group->members()->attach($user->id, ['nickname' => $user->username, 'is_admin' => true]);
        }
        for ($i = 0; $i < 10; $i++) {
            $buyer = $this->group->members->random();
            $purchase = Purchase::factory()->make();
            $user_ids = [];
            foreach ($users->except($buyer->id) as $user) {
                if (rand(0, 1) == 0)
                    $user_ids[] = ['user_id' => $user->id];
            }
            if (count($user_ids) == 0) $user_ids[]['user_id'] = $this->group->members->random()->id;

            $response = $this->actingAs($buyer, 'api')
                ->postJson(route('purchases.store'), [
                    'name' => $purchase->name,
                    'group' => $this->group->id,
                    'amount' => $purchase->amount,
                    'receivers' => $user_ids
                ]);
            $response->assertStatus(204);
        }
        $this->group->recalculateBalances();
        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertTrue(0 == $balance);
    }

    /**
     * Testing transactions created while deleting members
     * The action should not be authorized
     * @test
     * @return void
     */
    public function leaveGroupWithNegativeBalance()
    {
        foreach ($this->group->members as $member) {
            if ((int)$member->member_data->balance < 0) {
                $user = $member;
                break;
            }
        }
        $this->assertNotEmpty($user);
        $response = $this->actingAs($user, 'api')
            ->postJson(route('member.delete', ['group' => $this->group->id]), [
                'member_id' => $user->id
            ]);
        $response->assertStatus(400);
        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertTrue(0 == $balance);
    }

    /**
     * Testing transactions created while deleting members
     * Payments should be added for every member.
     * @test
     * @return void
     */
    public function leaveGroupWithPositiveBalance()
    {
        foreach ($this->group->members as $member) {
            if ($member->member_data->balance > 0) {
                $user = $member;
                break;
            }
        }
        $response = $this->actingAs($user, 'api')
            ->postJson(route('member.delete', ['group' => $this->group->id]), [
                'member_id' => $user->id
            ]);
        $response->assertStatus(204);


        //TODO: BUG
        //the transactions have no effect

        //$this->assertFalse($this->group->members->has($user->id));

        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertTrue(0 == $balance);
    }

    /**
     * Testing transactions created while deleting members
     * One payment should be added to the kicker.
     * @test
     * @return void
     */
    public function kickMember()
    {
        $admin = $this->group->admins->first();
        $member_to_kick = $this->group->members->except($admin->id)->random();
        $response = $this->actingAs($admin, 'api')
            ->postJson(route('member.delete', ['group' => $this->group->id]), [
                'member_id' => $member_to_kick->id
            ]);
        $response->assertStatus(200);

        //TODO: BUG
        //the transactions have no effect

        //$this->assertFalse($this->group->members->has($member_to_kick->id));

        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertTrue(0 == $balance);
    }
}
