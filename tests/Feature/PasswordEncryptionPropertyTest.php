<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordEncryptionPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 5: Password encryption
     * 
     * **Validates: Requirements 1.5**
     * 
     * For any password, when stored in the database, the stored value 
     * should not equal the plaintext password and should be a valid bcrypt hash.
     */
    public function test_password_is_encrypted_with_bcrypt(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($passwordRaw) {
                // Generate a valid password (at least 8 characters)
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'password123';
                }
                
                // Create a unique email for each test iteration
                $email = uniqid() . '@example.com';
                
                // Create user with password
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'pet_owner',
                ]);

                // Retrieve the user from the database
                $retrievedUser = User::where('email', $email)->first();
                
                // Property 1: Password should NOT be stored in plaintext
                $this->assertNotEquals(
                    $password, 
                    $retrievedUser->password, 
                    "Password should not be stored in plaintext"
                );
                
                // Property 2: Stored password should be a valid bcrypt hash
                // Bcrypt hashes start with $2y$ and are 60 characters long
                $this->assertMatchesRegularExpression(
                    '/^\$2y\$/', 
                    $retrievedUser->password,
                    "Password should be a bcrypt hash starting with \$2y\$"
                );
                
                $this->assertEquals(
                    60, 
                    strlen($retrievedUser->password),
                    "Bcrypt hash should be exactly 60 characters long"
                );
                
                // Property 3: The hash should be verifiable with the original password
                $this->assertTrue(
                    Hash::check($password, $retrievedUser->password),
                    "Stored hash should be verifiable with the original password"
                );
                
                // Property 4: The hash should NOT verify with a different password
                $differentPassword = $password . 'different';
                $this->assertFalse(
                    Hash::check($differentPassword, $retrievedUser->password),
                    "Stored hash should not verify with a different password"
                );
                
                // Property 5: Each hash should be unique (bcrypt uses salt)
                // Create another user with the same password
                $email2 = uniqid() . '_2@example.com';
                $user2 = User::create([
                    'name' => 'Test User 2',
                    'email' => $email2,
                    'password' => Hash::make($password),
                    'role' => 'pet_owner',
                ]);
                
                $retrievedUser2 = User::where('email', $email2)->first();
                
                // Even with the same password, hashes should be different (due to salt)
                $this->assertNotEquals(
                    $retrievedUser->password,
                    $retrievedUser2->password,
                    "Bcrypt should generate unique hashes for the same password (salt)"
                );
                
                // But both should verify with the same password
                $this->assertTrue(
                    Hash::check($password, $retrievedUser2->password),
                    "Second hash should also verify with the original password"
                );
            });
    }

    /**
     * Additional property test: Password encryption via model casting
     * 
     * Tests that the 'hashed' cast on the password attribute works correctly.
     */
    public function test_password_hashed_cast_encrypts_automatically(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($passwordRaw) {
                // Generate a valid password
                $password = substr($passwordRaw, 0, 50);
                if (strlen($password) < 8) {
                    $password = 'testpass123';
                }
                
                $email = uniqid() . '@example.com';
                
                // Create user with plaintext password (model should hash it via cast)
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => $password, // Plaintext - should be hashed by model
                    'role' => 'pet_owner',
                ]);

                // Retrieve from database
                $retrievedUser = User::where('email', $email)->first();
                
                // Verify password was hashed automatically
                $this->assertNotEquals(
                    $password,
                    $retrievedUser->password,
                    "Password should be automatically hashed by model cast"
                );
                
                // Verify it's a valid bcrypt hash
                $this->assertMatchesRegularExpression(
                    '/^\$2y\$/',
                    $retrievedUser->password,
                    "Auto-hashed password should be bcrypt format"
                );
                
                // Verify the hash is verifiable
                $this->assertTrue(
                    Hash::check($password, $retrievedUser->password),
                    "Auto-hashed password should be verifiable"
                );
            });
    }
}
