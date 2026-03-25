<?php

namespace Tests\Feature\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Domains\Auth\Services\AuthService;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_authenticate_with_valid_credentials()
    {
        $user = User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'test@example.com',
            'phone' => '01714532308',
            'password' => Hash::make('password'),
        ]);

        $service = new AuthService();

        $result = $service->authenticate([
            'login' => 'text@example.com',
            'password' => 'password',
        ], '127.0.0.1');
        $this->assertTrue($result['success']);
        $this->assertEquals($user->id, $result['data']['user']->id);
        $this->assertNotEmpty($result['data']['token']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'phone' => '01784568254',
            'password' => Hash::make('secret'),
        ]);

        $service = new AuthService();

        $result = $service->authenticate([
            'login' => 'hacker@example.com',
            'password' => 'wrong-password',
        ], '127.0.0.1');

        $this->assertFalse($result['success']);
        $this->assertEquals(401, $result['code']);
    }
}
