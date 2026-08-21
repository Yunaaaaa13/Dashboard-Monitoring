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

        $response->assertStatus(200);
    }

    public function test_input_purchasing_requires_login(): void
    {
        $this->get('/purchasing/input')->assertRedirect('/login');
    }

    public function test_ezrunner_webhook_requires_integration_key(): void
    {
        $this->postJson('/api/ezrunner/sync')->assertUnauthorized();
    }
}
