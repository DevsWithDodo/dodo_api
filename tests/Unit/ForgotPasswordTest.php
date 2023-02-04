<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Group;
use App\Transactions\Payment;
use App\User;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;
    protected Group $group;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
        $this->group = Group::factory()->create(['name' => 'Test Group']);
        $users = User::factory()->count(2)->create(['default_currency' => 'EUR']);
        $this->user = $users[0];
        foreach ($users as $user) {
            $this->group->members()->attach($user->id, ['nickname' => encrypt($user->username), 'balance' => encrypt('0')]);
        }
        Payment::factory()->count(5)->create([
            'group_id' => $this->group->id,
            'payer_id' => $users[0]->id,
            'taker_id' => $users[1]->id
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function forgotPasswordTestSuccess()
    {
        $response = $this->put(route('user.forgot_password', ['username' =>$this->user->username]), [
            'group_count' => 1,
            'currency' => 'EUR',
            'groups' => [[
                'name' => 'Test Group',
                'nickname' => $this->user->username,
                'balance' => $this->group->member($this->user->id)->member_data->balance,
                'last_transaction_amount' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->amount,
                'last_transaction_date' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->date
            ]]
        ]);
        $response->assertStatus(200);

        $response = $this->put(json_decode($response->getContent(), true)['url'], [
            'password' => "1234",
            'password_confirmation' => "1234"
        ]);
        $response->assertStatus(204);
    }

    /**
     * @test
     * @return void
     */
    public function forgotPasswordTestSuccessWithError()
    {
        $response = $this->put(route('user.forgot_password', ['username' =>$this->user->username]), [
            'group_count' => 1,
            'currency' => 'HUF',
            'groups' => [[
                'name' => 'Test Group',
                'nickname' => $this->user->username,
                'balance' => $this->group->member($this->user->id)->member_data->balance,
                'last_transaction_amount' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->amount,
                'last_transaction_date' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->date
            ]]
        ]);
        $response->assertStatus(200);

        $response = $this->put(route('user.forgot_password', ['username' =>$this->user->username]), [
            'group_count' => 1,
            'currency' => 'EUR',
            'groups' => [[
                'name' => 'Test Group',
                'nickname' => 'not the nickname',
                'balance' => $this->group->member($this->user->id)->member_data->balance,
                'last_transaction_amount' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->amount,
                'last_transaction_date' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->date
            ]]
        ]);
        $response->assertStatus(200);
    }

        /**
     * @test
     * @return void
     */
    public function forgotPasswordTestFails()
    {
        $response = $this->put(route('user.forgot_password', ['username' =>$this->user->username]), [
            'group_count' => 1,
            'currency' => 'EUR',
            'groups' => [[
                'name' => 'Not the group name',
                'nickname' => $this->user->username,
                'balance' => $this->group->member($this->user->id)->member_data->balance,
                'last_transaction_amount' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->amount,
                'last_transaction_date' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->date
            ]]
        ]);
        $response->assertStatus(400);

        $response = $this->put(route('user.forgot_password', ['username' =>$this->user->username]), [
            'group_count' => 1,
            'currency' => 'HUF',
            'groups' => [[
                'name' => 'Test Group',
                'nickname' => 'not the group name',
                'balance' => $this->group->member($this->user->id)->member_data->balance,
                'last_transaction_amount' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->amount,
                'last_transaction_date' => $this->group->payments()->orderBy('updated_at', 'desc')->first()->date
            ]]
        ]);
        $response->assertStatus(400);

    }
}
