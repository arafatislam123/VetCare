<?php

namespace Tests\Feature;

use Tests\TestCase;

class SetupTest extends TestCase
{
    /**
     * Test that Inertia is properly configured.
     */
    public function test_inertia_is_configured(): void
    {
        $this->assertTrue(class_exists(\Inertia\Inertia::class));
    }

    /**
     * Test that Redis connection is available.
     */
    public function test_redis_connection(): void
    {
        $this->assertTrue(extension_loaded('redis') || class_exists(\Predis\Client::class));
    }

    /**
     * Test that Vue and Vite are configured.
     */
    public function test_frontend_dependencies_exist(): void
    {
        $this->assertFileExists(base_path('vite.config.js'));
        $this->assertFileExists(base_path('tailwind.config.js'));
        $this->assertFileExists(base_path('resources/js/app.js'));
        $this->assertFileExists(base_path('resources/css/app.css'));
    }

    /**
     * Test that Eris property-based testing library is available.
     */
    public function test_eris_is_available(): void
    {
        $this->assertTrue(trait_exists(\Eris\TestTrait::class));
    }

    /**
     * Test that broadcasting is configured.
     */
    public function test_broadcasting_is_configured(): void
    {
        $this->assertEquals('pusher', config('broadcasting.default'));
    }
}
