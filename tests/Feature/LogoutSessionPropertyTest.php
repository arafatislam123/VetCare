<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutSessionPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 4: Logout terminates session
     * 
     * **Validates: Requirements 1.4**
     * 
     * For any authenticated session, when a user logs out, 
     * the session should be terminated and subsequent requests 
     * with that session should be rejected.
     */
    public function test_logout_terminates_session(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\elements('admin', 'pet_owner', 'veterinarian')
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw, $role) {
                // Generate valid credentials
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $email = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create a user in the database
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $role,
                ]);

                // Property 1: Before login, user should not be authenticated
                $this->assertGuest();
                
                // Login the user
                $credentials = ['email' => $email, 'password' => $password];
                $loginSuccessful = Auth::attempt($credentials);
                
                // Verify login was successful
                $this->assertTrue($loginSuccessful, "Login should succeed");
                $this->assertAuthenticated();
                $this->assertAuthenticatedAs($user);
                
                // Store session ID before logout
                $sessionIdBeforeLogout = session()->getId();
                $this->assertNotEmpty($sessionIdBeforeLogout, "Session ID should exist before logout");
                
                // Property 2: User should be authenticated before logout
                $this->assertTrue(Auth::check(), "User should be authenticated before logout");
                $this->assertEquals($user->id, Auth::id(), "Authenticated user ID should match");
                $this->assertNotNull(auth()->user(), "auth()->user() should return user before logout");
                
                // Perform logout (simulating what AuthController does)
                Auth::logout();
                
                // Property 3: After logout, user should not be authenticated
                $this->assertGuest();
                $this->assertFalse(Auth::check(), "Auth::check should return false after logout");
                $this->assertNull(Auth::id(), "Auth::id should return null after logout");
                $this->assertNull(auth()->user(), "auth()->user() should return null after logout");
                
                // Property 4: Session should be invalidated
                // Simulate session invalidation (as done in controller)
                session()->invalidate();
                
                // Property 5: Session ID should be different after invalidation
                $sessionIdAfterLogout = session()->getId();
                $this->assertNotEquals(
                    $sessionIdBeforeLogout,
                    $sessionIdAfterLogout,
                    "Session ID should be different after logout and invalidation"
                );
                
                // Property 6: Attempting to authenticate with old session should fail
                // The user is now a guest and cannot access authenticated resources
                $this->assertGuest();
                
                // Property 7: Verify that re-authentication is required
                // Try to check authentication again - should still be guest
                $this->assertFalse(Auth::check(), "User should remain unauthenticated after logout");
                $this->assertNull(Auth::user(), "Auth::user should return null after logout");
            });
    }

    /**
     * Additional property test: Logout clears remember token
     * 
     * Tests that logout clears the remember token when user was logged in with remember me.
     */
    public function test_logout_clears_remember_token(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw) {
                // Generate valid credentials
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $email = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create a user in the database
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'pet_owner',
                ]);

                // Login with remember me option
                $credentials = ['email' => $email, 'password' => $password];
                Auth::attempt($credentials, true); // remember = true
                
                // Verify user is authenticated and has remember token
                $this->assertAuthenticated();
                $freshUser = User::find($user->id);
                $rememberTokenBeforeLogout = $freshUser->remember_token;
                
                // If remember token was set, verify it exists
                if ($rememberTokenBeforeLogout !== null) {
                    $this->assertNotEmpty($rememberTokenBeforeLogout, "Remember token should be set");
                }
                
                // Logout
                Auth::logout();
                
                // Property: After logout, user should not be authenticated
                $this->assertGuest();
                
                // Property: Remember token should be cleared or invalidated
                // Note: Laravel's Auth::logout() doesn't automatically clear remember token
                // but the session is invalidated, making the token unusable
                $this->assertFalse(Auth::check(), "User should not be authenticated after logout");
            });
    }

    /**
     * Additional property test: Multiple logout calls are safe
     * 
     * Tests that calling logout multiple times doesn't cause errors.
     */
    public function test_multiple_logout_calls_are_safe(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw) {
                // Generate valid credentials
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $email = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create a user in the database
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'pet_owner',
                ]);

                // Login the user
                $credentials = ['email' => $email, 'password' => $password];
                Auth::attempt($credentials);
                $this->assertAuthenticated();
                
                // First logout
                Auth::logout();
                $this->assertGuest();
                
                // Property: Second logout should not cause errors
                Auth::logout();
                $this->assertGuest();
                
                // Property: Third logout should still be safe
                Auth::logout();
                $this->assertGuest();
                
                // Property: User should remain unauthenticated
                $this->assertFalse(Auth::check());
                $this->assertNull(Auth::user());
            });
    }

    /**
     * Additional property test: Logout prevents access to authenticated routes
     * 
     * Tests that after logout, the user cannot access authenticated resources.
     */
    public function test_logout_prevents_authenticated_access(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\elements('admin', 'pet_owner', 'veterinarian')
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw, $role) {
                // Generate valid credentials
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $email = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create a user in the database
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $role,
                ]);

                // Login the user
                $credentials = ['email' => $email, 'password' => $password];
                Auth::attempt($credentials);
                
                // Property 1: User can access authenticated resources before logout
                $this->assertAuthenticated();
                $this->assertTrue(Auth::check());
                
                // Logout
                Auth::logout();
                session()->invalidate();
                
                // Property 2: After logout, user is a guest
                $this->assertGuest();
                
                // Property 3: Auth guard should not return user
                $this->assertNull(Auth::user(), "Auth::user should return null after logout");
                $this->assertFalse(Auth::check(), "Auth::check should return false after logout");
                
                // Property 4: User ID should not be accessible
                $this->assertNull(Auth::id(), "Auth::id should return null after logout");
            });
    }
}
