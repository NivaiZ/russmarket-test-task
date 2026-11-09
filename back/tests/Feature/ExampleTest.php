<?php

namespace Tests\Feature;

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

    public function test_browser_password_is_checked_against_env_without_bcrypt(): void
    {
        config([
            'auth.basic_disabled' => false,
            'auth.basic_email' => 'admin',
            'auth.basic_password' => 'secret-password',
        ]);

        $this->get('/')->assertUnauthorized();

        $this->withBasicAuth('admin', 'wrong')
            ->get('/')
            ->assertUnauthorized();

        $this->withBasicAuth('admin', 'secret-password')
            ->get('/')
            ->assertOk();
    }
}
