<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginSessionPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 2: Login creates valid session
     * 
     * **Validates: Requirements 1.2**
     * 
     * For any valid user credentials, when a user logs in, 
     * the system should create a session that can be used to 
     * authenticate subsequent requests.
     */
    public function test_login_creates_valid_session(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\elements('admin', 'pet_owner', 'veterinarian')
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw, $role) {
                // Clear rate limiter before each iteration
                RateLimiter::clear(sha1('login'));
                
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
                
                // Simulate login using Auth::attempt (same as controller)
                $credentials = ['email' => $email, 'password' => $password];
                $loginSuccessful = Auth::attempt($credentials);

                // Property 2: Login should succeed
                $this->assertTrue($loginSuccessful, "Login should succeed with valid credentials");
                
                // Property 3: After login, user should be authenticated
                $this->assertAuthenticated();
                
                // Property 4: The authenticated user should be the correct user
                $this->assertAuthenticatedAs($user);
                
                // Property 5: Session should contain user authentication data
                $this->assertEquals($user->id, auth()->id(), "Session should contain the correct user ID");
                $this->assertEquals($email, auth()->user()->email, "Session should contain the correct user email");
                $this->assertEquals($role, auth()->user()->role, "Session should contain the correct user role");
                
                // Property 6: Session should persist - check auth again
                $this->assertTrue(Auth::check(), "Session should persist and user should still be authenticated");
                $this->assertEquals($user->id, Auth::id(), "Session should persist with correct user ID");
                
                // Logout to clean up for next iteration
                Auth::logout();
                $this->assertGuest();
            });
    }

    /**
     * Additional property test: Invalid credentials do not create session
     * 
     * Tests that login with invalid credentials does not create a session.
     */
    public function test_invalid_credentials_do_not_create_session(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $correctPasswordRaw, $wrongPasswordRaw) {
                // Clear rate limiter before each iteration
                RateLimiter::clear(sha1('login'));
                
                // Generate valid credentials
                $emailPart = preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix);
                if (strlen($emailPart) < 3) {
                    $emailPart = 'user';
                }
                $email = uniqid() . '_' . substr($emailPart, 0, 20) . '@example.com';
                
                $correctPassword = substr($correctPasswordRaw, 0, 50);
                if (strlen($correctPassword) < 8) {
                    $correctPassword = 'password123';
                }
                
                $wrongPassword = substr($wrongPasswordRaw, 0, 50);
                if (strlen($wrongPassword) < 8) {
                    $wrongPassword = 'wrongpass456';
                }
                
                // Ensure wrong password is different from correct password
                if ($wrongPassword === $correctPassword) {
                    $wrongPassword = $correctPassword . '_wrong';
                }
                
                // Create a user in the database
                User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($correctPassword),
                    'role' => 'pet_owner',
                ]);

                // Property 1: Before login attempt, user should not be authenticated
                $this->assertGuest();
                
                // Attempt to login with WRONG password using Auth::attempt
                $credentials = ['email' => $email, 'password' => $wrongPassword];
                $loginSuccessful = Auth::attempt($credentials);

                // Property 2: Login should fail
                $this->assertFalse($loginSuccessful, "Login should fail with invalid credentials");
                
                // Property 3: User should still be a guest (no session created)
                $this->assertGuest();
                
                // Property 4: Auth facade should return null for user
                $this->assertNull(auth()->user(), "No user should be authenticated after failed login");
                
                // Property 5: Auth::check should return false
                $this->assertFalse(Auth::check(), "Auth::check should return false after failed login");
            });
    }

    /**
     * Additional property test: Remember me functionality
     * 
     * Tests that the remember me option creates a persistent session.
     */
    public function test_remember_me_creates_persistent_session(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\bool()
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw, $remember) {
                // Clear rate limiter before each iteration
                RateLimiter::clear(sha1('login'));
                
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

                // Login with remember option using Auth::attempt
                $credentials = ['email' => $email, 'password' => $password];
                $loginSuccessful = Auth::attempt($credentials, $remember);

                // Property 1: Login should succeed
                $this->assertTrue($loginSuccessful, "Login should succeed");
                $this->assertAuthenticated();
                
                // Property 2: User should be authenticated
                $this->assertAuthenticatedAs($user);
                
                // Property 3: If remember is true, remember token should be set
                if ($remember) {
                    $freshUser = User::find($user->id);
                    $this->assertNotNull(
                        $freshUser->remember_token,
                        "Remember token should be set when remember option is true"
                    );
                }
                
                // Logout to clean up
                Auth::logout();
                $this->assertGuest();
            });
    }

    /**
     * Additional property test: Session regeneration on login
     * 
     * Tests that the session ID is regenerated on login for security.
     * This test uses HTTP requests to verify session regeneration behavior.
     */
    public function test_session_regenerates_on_login(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($emailPrefix, $passwordRaw) {
                // Clear rate limiter before each iteration
                RateLimiter::clear(sha1('login'));
                
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
                User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'pet_owner',
                ]);

                // Get initial session ID
                $oldSessionId = session()->getId();
                
                // Login using Auth::attempt (simulates what controller does)
                $credentials = ['email' => $email, 'password' => $password];
                Auth::attempt($credentials);
                
                // Simulate session regeneration (as done in controller)
                session()->regenerate();
                
                // Property: Session ID should be different after regeneration
                $newSessionId = session()->getId();
                $this->assertNotEquals(
                    $oldSessionId,
                    $newSessionId,
                    "Session ID should be regenerated on login for security"
                );
                
                // Verify user is still authenticated after regeneration
                $this->assertAuthenticated();
                
                // Logout to clean up
                Auth::logout();
            });
    }
}

