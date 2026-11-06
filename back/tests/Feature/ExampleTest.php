<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
    }

    public function test_browser_password_is_checked_on_the_backend(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        User::factory()->create([
            'email' => 'ilya-94',
            'password' => 'secret-password',
        ]);

        $this->get('/')->assertUnauthorized();

        $this->withBasicAuth('ilya-94', 'wrong')
            ->get('/')
            ->assertUnauthorized();

        $this->withBasicAuth('ilya-94', 'secret-password')
            ->get('/')
            ->assertOk();
    }
}
