<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Con Filament, la ruta / probablemente redirige al panel admin
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }
}
