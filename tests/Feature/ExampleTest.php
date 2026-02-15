<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * ExampleTest - Basic application health tests
 */
class ExampleTest extends TestCase
{
    /**
     * Test the application redirects to login when not authenticated.
     */
    public function test_the_application_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        // Homepage redirects unauthenticated users to login
        $response->assertRedirect();
    }

    /**
     * Test the login page loads.
     */
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
