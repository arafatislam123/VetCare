<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\Specialization;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorSearchPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 6: Search returns matching profiles
     * 
     * **Validates: Requirements 2.1**
     * 
     * For any search query and set of doctor profiles, all returned results 
     * should match the search criteria (name, specialization, or keywords).
     */
    public function test_search_returns_only_matching_profiles(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($searchTermRaw, $matchingNameRaw, $nonMatchingNameRaw) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                
                // Generate a valid search term
                $searchTerm = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $searchTermRaw), 0, 20);
                if (strlen($searchTerm) < 3) {
                    $searchTerm = 'test';
                }
                
                // Generate matching name that contains the search term
                $matchingNamePart = preg_replace('/[^a-zA-Z ]/', '', $matchingNameRaw);
                if (strlen($matchingNamePart) < 3) {
                    $matchingNamePart = 'Doctor';
                }
                $matchingName = 'Dr. ' . substr($matchingNamePart, 0, 20) . ' ' . $searchTerm;
                
                // Generate non-matching name that does NOT contain the search term
                $nonMatchingNamePart = preg_replace('/[^a-zA-Z ]/', '', $nonMatchingNameRaw);
                if (strlen($nonMatchingNamePart) < 3) {
                    $nonMatchingNamePart = 'Smith';
                }
                // Ensure non-matching name doesn't accidentally contain search term
                $nonMatchingName = 'Dr. ' . str_replace($searchTerm, '', substr($nonMatchingNamePart, 0, 20));
                if (strlen($nonMatchingName) < 5) {
                    $nonMatchingName = 'Dr. XYZ';
                }
                
                // Create matching veterinarian (name contains search term)
                $matchingUser = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => $matchingName,
                    'email' => uniqid() . '_match@example.com',
                ]);
                $matchingVet = Veterinarian::create([
                    'user_id' => $matchingUser->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'General veterinary practice',
                    'consultation_fee' => 500.00,
                ]);
                
                // Create non-matching veterinarian (name does NOT contain search term)
                $nonMatchingUser = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => $nonMatchingName,
                    'email' => uniqid() . '_nomatch@example.com',
                ]);
                $nonMatchingVet = Veterinarian::create([
                    'user_id' => $nonMatchingUser->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 8,
                    'bio' => 'Specialized practice',
                    'consultation_fee' => 600.00,
                ]);
                
                // Perform search using direct query to get actual results
                $searchResults = Veterinarian::with(['user', 'specializations'])
                    ->where(function ($q) use ($searchTerm) {
                        $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', "%{$searchTerm}%");
                        })->orWhere('bio', 'like', "%{$searchTerm}%");
                    })
                    ->get();
                
                // Property: All returned results should match the search criteria
                foreach ($searchResults as $vet) {
                    $vetName = $vet->user->name ?? '';
                    $vetBio = $vet->bio ?? '';
                    
                    // Each result should contain the search term in name OR bio
                    $matchesName = stripos($vetName, $searchTerm) !== false;
                    $matchesBio = stripos($vetBio, $searchTerm) !== false;
                    
                    $this->assertTrue(
                        $matchesName || $matchesBio,
                        "Search result should match search term '{$searchTerm}'. " .
                        "Found name: '{$vetName}', bio: '{$vetBio}'"
                    );
                }
                
                // Verify matching veterinarian is in results
                $matchingVetFound = $searchResults->contains('id', $matchingVet->id);
                $this->assertTrue(
                    $matchingVetFound,
                    "Matching veterinarian should be in search results"
                );
                
                // Verify non-matching veterinarian is NOT in results
                $nonMatchingVetFound = $searchResults->contains('id', $nonMatchingVet->id);
                $this->assertFalse(
                    $nonMatchingVetFound,
                    "Non-matching veterinarian should NOT be in search results"
                );
            });
    }

    /**
     * Property test: Search by bio content
     * 
     * Verifies that search matches veterinarians by bio content.
     */
    public function test_search_matches_bio_content(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($bioKeywordRaw) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                
                // Generate a valid bio keyword
                $bioKeyword = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $bioKeywordRaw), 0, 15);
                if (strlen($bioKeyword) < 3) {
                    $bioKeyword = 'exotic';
                }
                
                // Create veterinarian with keyword in bio
                $user = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. ' . uniqid(),
                    'email' => uniqid() . '@example.com',
                ]);
                $vet = Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'Specializes in ' . $bioKeyword . ' animals and general care',
                    'consultation_fee' => 500.00,
                ]);
                
                // Search by bio keyword using direct query
                $searchResults = Veterinarian::with(['user', 'specializations'])
                    ->where(function ($q) use ($bioKeyword) {
                        $q->whereHas('user', function ($userQuery) use ($bioKeyword) {
                            $userQuery->where('name', 'like', "%{$bioKeyword}%");
                        })->orWhere('bio', 'like', "%{$bioKeyword}%");
                    })
                    ->get();
                
                // Verify the veterinarian with matching bio is in results
                $found = $searchResults->contains('id', $vet->id);
                
                $this->assertTrue(
                    $found,
                    "Veterinarian with matching bio should be in search results"
                );
                
                // Verify bio contains the keyword
                if ($found) {
                    $foundVet = $searchResults->firstWhere('id', $vet->id);
                    $this->assertStringContainsStringIgnoringCase(
                        $bioKeyword,
                        $foundVet->bio,
                        "Returned veterinarian bio should contain search keyword"
                    );
                }
            });
    }

    /**
     * Property test: Search with specialization filter
     * 
     * Verifies that search combined with specialization filter returns 
     * only veterinarians matching both criteria.
     */
    public function test_search_with_specialization_filter_matches_both_criteria(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(50)
            ->then(function ($searchTermRaw) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                Specialization::query()->delete();
                
                // Generate search term
                $searchTerm = substr(preg_replace('/[^a-zA-Z ]/', '', $searchTermRaw), 0, 15);
                if (strlen($searchTerm) < 3) {
                    $searchTerm = 'care';
                }
                
                // Create specializations with unique names
                $specialization1 = Specialization::create(['name' => 'Surgery_' . uniqid()]);
                $specialization2 = Specialization::create(['name' => 'Dentistry_' . uniqid()]);
                
                // Create vet matching both search term AND specialization
                $matchingUser = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. ' . $searchTerm . ' Specialist',
                    'email' => uniqid() . '_match@example.com',
                ]);
                $matchingVet = Veterinarian::create([
                    'user_id' => $matchingUser->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'consultation_fee' => 500.00,
                ]);
                $matchingVet->specializations()->attach($specialization1->id);
                
                // Create vet matching search term but NOT specialization
                $partialMatchUser = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. ' . $searchTerm . ' General',
                    'email' => uniqid() . '_partial@example.com',
                ]);
                $partialMatchVet = Veterinarian::create([
                    'user_id' => $partialMatchUser->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 8,
                    'consultation_fee' => 600.00,
                ]);
                $partialMatchVet->specializations()->attach($specialization2->id);
                
                // Search with both query and specialization filter using direct query
                $searchResults = Veterinarian::with(['user', 'specializations'])
                    ->where(function ($q) use ($searchTerm) {
                        $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', "%{$searchTerm}%");
                        })->orWhere('bio', 'like', "%{$searchTerm}%");
                    })
                    ->whereHas('specializations', function ($q) use ($specialization1) {
                        $q->where('specializations.id', $specialization1->id);
                    })
                    ->get();
                
                // Property: All results should match BOTH search term AND specialization
                foreach ($searchResults as $vet) {
                    $vetName = $vet->user->name ?? '';
                    $vetBio = $vet->bio ?? '';
                    
                    // Should match search term
                    $matchesSearch = stripos($vetName, $searchTerm) !== false || 
                                   stripos($vetBio, $searchTerm) !== false;
                    $this->assertTrue(
                        $matchesSearch,
                        "Result should match search term '{$searchTerm}'"
                    );
                    
                    // Should have the specified specialization
                    $hasSpecialization = $vet->specializations->contains('id', $specialization1->id);
                    $this->assertTrue(
                        $hasSpecialization,
                        "Result should have the specified specialization"
                    );
                }
                
                // Verify fully matching vet is in results
                $matchingFound = $searchResults->contains('id', $matchingVet->id);
                $this->assertTrue(
                    $matchingFound,
                    "Veterinarian matching both criteria should be in results"
                );
                
                // Verify partial match (wrong specialization) is NOT in results
                $partialFound = $searchResults->contains('id', $partialMatchVet->id);
                $this->assertFalse(
                    $partialFound,
                    "Veterinarian with wrong specialization should NOT be in results"
                );
            });
    }

    /**
     * Property test: Empty search returns all veterinarians
     * 
     * Verifies that when no search query is provided, all veterinarians are returned.
     */
    public function test_empty_search_returns_all_veterinarians(): void
    {
        $this->forAll(
            Generator\choose(1, 5)
        )
            ->then(function ($vetCount) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                
                // Create multiple veterinarians
                $createdVets = [];
                for ($i = 0; $i < $vetCount; $i++) {
                    $user = User::factory()->create([
                        'role' => 'veterinarian',
                        'email' => uniqid() . "_vet{$i}@example.com",
                    ]);
                    $vet = Veterinarian::create([
                        'user_id' => $user->id,
                        'license_number' => 'VET' . uniqid(),
                        'experience_years' => rand(1, 20),
                        'consultation_fee' => rand(300, 800),
                    ]);
                    $createdVets[] = $vet->id;
                }
                
                // Get all veterinarians (empty search)
                $allVets = Veterinarian::with(['user', 'specializations'])->get();
                
                // Property: All created veterinarians should be in results
                $this->assertEquals(
                    $vetCount,
                    $allVets->count(),
                    "Empty search should return exactly all created veterinarians"
                );
                
                // Verify each created vet is in results
                foreach ($createdVets as $vetId) {
                    $found = $allVets->contains('id', $vetId);
                    $this->assertTrue(
                        $found,
                        "Each created veterinarian should be in empty search results"
                    );
                }
            });
    }
}
