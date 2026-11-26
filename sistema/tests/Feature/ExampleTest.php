<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_redirect_for_guests(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
