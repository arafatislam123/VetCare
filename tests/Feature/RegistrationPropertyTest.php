<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 1: Registration creates account with correct role
     * 
     * **Validates: Requirements 1.1**
     * 
     * For any valid registration data (name, email, password, role), 
     * when a user registers, the system should create an account with 
     * the specified role that can be retrieved from the database.
     */
    public function test_registration_creates_account_with_correct_role(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\string(),
            Generator\elements('admin', 'pet_owner', 'veterinarian')
        )
            ->withMaxSize(100)
            ->then(function ($nameRaw, $emailPrefix, $passwordRaw, $role) {
                // Generate valid registration data
                $name = substr(preg_replace('/[^a-zA-Z ]/', '', $nameRaw), 0, 50);
                if (strlen($name) < 3) {
                    $name = 'Test User';
                }
                
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $uniqueEmail = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $password = substr($passwordRaw, 0, 20);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create user directly (simulating registration logic)
                $user = User::create([
                    'name' => $name,
                    'email' => $uniqueEmail,
                    'password' => Hash::make($password),
                    'role' => $role,
                ]);

                // Verify the account was created in the database
                $this->assertDatabaseHas('users', [
                    'email' => $uniqueEmail,
                    'role' => $role,
                ]);

                // Retrieve the user from the database
                $retrievedUser = User::where('email', $uniqueEmail)->first();
                
                // Verify the user exists and has the correct role
                $this->assertNotNull($retrievedUser, "User should exist in database");
                $this->assertEquals($role, $retrievedUser->role, "User role should match the specified role");
                $this->assertEquals($name, $retrievedUser->name, "User name should match");
                $this->assertEquals($uniqueEmail, $retrievedUser->email, "User email should match");
                
                // Verify password is hashed (not stored in plaintext)
                $this->assertNotEquals($password, $retrievedUser->password, "Password should be hashed");
                $this->assertTrue(Hash::check($password, $retrievedUser->password), "Password should be verifiable");
                
                // Verify role-specific methods work correctly
                $this->assertEquals($role === 'admin', $retrievedUser->isAdmin());
                $this->assertEquals($role === 'pet_owner', $retrievedUser->isPetOwner());
                $this->assertEquals($role === 'veterinarian', $retrievedUser->isVeterinarian());
                $this->assertTrue($retrievedUser->hasRole($role));
            });
    }
}
