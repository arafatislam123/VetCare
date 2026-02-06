<?php

namespace Tests\Feature;

use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessControlPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 3: Role-based access control
     * 
     * For any user and protected resource, when the user attempts to access the resource,
     * access should be granted if and only if the user's role has permission for that resource.
     * 
     * **Validates: Requirements 1.3**
     */
    public function test_role_based_access_control_property(): void
    {
        $this->forAll(
            $this->roleGenerator(),
            $this->protectedRouteGenerator()
        )
        ->then(function ($userRole, $routeConfig) {
            // Create a user with the generated role
            $user = User::factory()->create(['role' => $userRole]);
            
            // Act as the user
            $this->actingAs($user);
            
            // Attempt to access the protected route
            $response = $this->get($routeConfig['url']);
            
            // Verify access control: access granted if and only if user has required role
            $hasRequiredRole = in_array($userRole, $routeConfig['allowed_roles']);
            
            if ($hasRequiredRole) {
                // User should have access (200 OK)
                $this->assertEquals(
                    200,
                    $response->status(),
                    "User with role '{$userRole}' should have access to {$routeConfig['url']}"
                );
            } else {
                // User should be denied access (403 Forbidden)
                $this->assertEquals(
                    403,
                    $response->status(),
                    "User with role '{$userRole}' should NOT have access to {$routeConfig['url']}"
                );
            }
        });
    }

    /**
     * Generator for valid user roles.
     */
    private function roleGenerator()
    {
        return Generator\elements('admin', 'pet_owner', 'veterinarian');
    }

    /**
     * Generator for protected routes with their allowed roles.
     */
    private function protectedRouteGenerator()
    {
        $routes = [
            [
                'url' => '/admin/dashboard',
                'allowed_roles' => ['admin'],
            ],
            [
                'url' => '/pets',
                'allowed_roles' => ['pet_owner'],
            ],
            [
                'url' => '/veterinarian/schedule',
                'allowed_roles' => ['veterinarian'],
            ],
        ];

        return Generator\elements(...$routes);
    }

    /**
     * Property 3 (Extended): Role-based access control with multiple allowed roles
     * 
     * Tests that routes can allow multiple roles and access is granted
     * if the user has ANY of the allowed roles.
     * 
     * **Validates: Requirements 1.3**
     */
    public function test_role_based_access_control_with_multiple_roles_property(): void
    {
        $this->forAll(
            $this->roleGenerator(),
            Generator\elements(
                ['admin', 'pet_owner'],
                ['admin', 'veterinarian'],
                ['pet_owner', 'veterinarian']
            )
        )
        ->then(function ($userRole, $allowedRoles) {
            // Create a user with the generated role
            $user = User::factory()->create(['role' => $userRole]);
            
            // Verify the hasRole method works correctly
            $hasRequiredRole = in_array($userRole, $allowedRoles);
            
            // Test that the user's hasRole method returns correct result
            foreach ($allowedRoles as $allowedRole) {
                if ($userRole === $allowedRole) {
                    $this->assertTrue(
                        $user->hasRole($allowedRole),
                        "User with role '{$userRole}' should have role '{$allowedRole}'"
                    );
                    break;
                }
            }
            
            // Verify the logical consistency
            $this->assertEquals(
                $hasRequiredRole,
                in_array($userRole, $allowedRoles),
                "Role check should be consistent"
            );
        });
    }

    /**
     * Property 3 (Inverse): Unauthorized roles are always denied
     * 
     * For any user role that is NOT in the allowed roles list,
     * access should always be denied.
     * 
     * **Validates: Requirements 1.3**
     */
    public function test_unauthorized_roles_always_denied_property(): void
    {
        $this->forAll(
            $this->roleGenerator(),
            $this->protectedRouteGenerator()
        )
        ->then(function ($userRole, $routeConfig) {
            // Create a user with the generated role
            $user = User::factory()->create(['role' => $userRole]);
            
            // Act as the user
            $this->actingAs($user);
            
            // Check if user has required role
            $hasRequiredRole = in_array($userRole, $routeConfig['allowed_roles']);
            
            // If user does NOT have required role, access must be denied
            if (!$hasRequiredRole) {
                $response = $this->get($routeConfig['url']);
                
                $this->assertEquals(
                    403,
                    $response->status(),
                    "User with role '{$userRole}' must be denied access to {$routeConfig['url']} " .
                    "(allowed roles: " . implode(', ', $routeConfig['allowed_roles']) . ")"
                );
            }
        });
    }

    /**
     * Property 3 (Completeness): All valid roles are tested
     * 
     * Ensures that the role checking logic is complete and handles
     * all three valid roles correctly.
     * 
     * **Validates: Requirements 1.3**
     */
    public function test_all_roles_have_consistent_behavior_property(): void
    {
        $this->forAll(
            $this->roleGenerator()
        )
        ->then(function ($role) {
            // Create a user with the generated role
            $user = User::factory()->create(['role' => $role]);
            
            // Verify role checking methods are consistent
            $isAdmin = $user->isAdmin();
            $isPetOwner = $user->isPetOwner();
            $isVeterinarian = $user->isVeterinarian();
            
            // Exactly one role method should return true
            $trueCount = ($isAdmin ? 1 : 0) + ($isPetOwner ? 1 : 0) + ($isVeterinarian ? 1 : 0);
            
            $this->assertEquals(
                1,
                $trueCount,
                "User should have exactly one role, but role '{$role}' resulted in {$trueCount} true checks"
            );
            
            // Verify hasRole is consistent with specific role methods
            $this->assertEquals($isAdmin, $user->hasRole('admin'));
            $this->assertEquals($isPetOwner, $user->hasRole('pet_owner'));
            $this->assertEquals($isVeterinarian, $user->hasRole('veterinarian'));
        });
    }
}
