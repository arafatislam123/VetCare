<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pet_owner',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'pet_owner',
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pet_owner',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'pet_owner',
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_model_has_role_methods(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petOwner = User::factory()->create(['role' => 'pet_owner']);
        $veterinarian = User::factory()->create(['role' => 'veterinarian']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isPetOwner());
        $this->assertFalse($admin->isVeterinarian());

        $this->assertFalse($petOwner->isAdmin());
        $this->assertTrue($petOwner->isPetOwner());
        $this->assertFalse($petOwner->isVeterinarian());

        $this->assertFalse($veterinarian->isAdmin());
        $this->assertFalse($veterinarian->isPetOwner());
        $this->assertTrue($veterinarian->isVeterinarian());

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($petOwner->hasRole('pet_owner'));
        $this->assertTrue($veterinarian->hasRole('veterinarian'));
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $petOwner = User::factory()->create(['role' => 'pet_owner']);

        $this->actingAs($petOwner);

        // Pet owner should not access admin routes
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_role_middleware_allows_authorized_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        // Admin should access admin routes
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_login_rate_limiting(): void
    {
        // Make 3 failed login attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 4th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }
}
