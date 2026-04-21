<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->getContent(), '/build/assets/app-')
            || str_contains($response->getContent(), '/resources/css/app.css')
        );
        $response->assertDontSee('href="/css/app.css"', false);
        $response->assertDontSee('href="http://localhost/css/app.css"', false);
        $response->assertDontSee('src="/js/app.js"', false);
        $response->assertDontSee('src="http://localhost/js/app.js"', false);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
