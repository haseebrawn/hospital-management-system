<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // 10 allowed per minute (see routes/web.php throttle:10,1)
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
