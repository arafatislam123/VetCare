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
                
                // Perform search
                $response = $this->get(route('doctors.search', ['query' => $searchTerm]));
                
                // Verify response is successful
                $response->assertStatus(200);
                
                // Get the returned veterinarians
                $returnedVets = $response->viewData('page')['props']['veterinarians']['data'];
                
                // Property: All returned results should match the search criteria
                foreach ($returnedVets as $vet) {
                    $vetName = $vet['user']['name'] ?? '';
                    $vetBio = $vet['bio'] ?? '';
                    
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
                $matchingVetFound = false;
                foreach ($returnedVets as $vet) {
                    if ($vet['id'] === $matchingVet->id) {
                        $matchingVetFound = true;
                        break;
                    }
                }
                $this->assertTrue(
                    $matchingVetFound,
                    "Matching veterinarian should be in search results"
                );
                
                // Verify non-matching veterinarian is NOT in results
                $nonMatchingVetFound = false;
                foreach ($returnedVets as $vet) {
                    if ($vet['id'] === $nonMatchingVet->id) {
                        $nonMatchingVetFound = true;
                        break;
                    }
                }
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
                
                // Search by bio keyword
                $response = $this->get(route('doctors.search', ['query' => $bioKeyword]));
                
                $response->assertStatus(200);
                
                // Get returned veterinarians
                $returnedVets = $response->viewData('page')['props']['veterinarians']['data'];
                
                // Verify the veterinarian with matching bio is in results
                $found = false;
                foreach ($returnedVets as $returnedVet) {
                    if ($returnedVet['id'] === $vet->id) {
                        $found = true;
                        // Verify bio contains the keyword
                        $this->assertStringContainsStringIgnoringCase(
                            $bioKeyword,
                            $returnedVet['bio'],
                            "Returned veterinarian bio should contain search keyword"
                        );
                        break;
                    }
                }
                
                $this->assertTrue(
                    $found,
                    "Veterinarian with matching bio should be in search results"
                );
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
                // Generate search term
                $searchTerm = substr(preg_replace('/[^a-zA-Z ]/', '', $searchTermRaw), 0, 15);
                if (strlen($searchTerm) < 3) {
                    $searchTerm = 'care';
                }
                
                // Create specializations
                $specialization1 = Specialization::create(['name' => 'Surgery']);
                $specialization2 = Specialization::create(['name' => 'Dentistry']);
                
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
                
                // Search with both query and specialization filter
                $response = $this->get(route('doctors.search', [
                    'query' => $searchTerm,
                    'specialization' => $specialization1->id,
                ]));
                
                $response->assertStatus(200);
                
                // Get returned veterinarians
                $returnedVets = $response->viewData('page')['props']['veterinarians']['data'];
                
                // Property: All results should match BOTH search term AND specialization
                foreach ($returnedVets as $vet) {
                    $vetName = $vet['user']['name'] ?? '';
                    $vetBio = $vet['bio'] ?? '';
                    
                    // Should match search term
                    $matchesSearch = stripos($vetName, $searchTerm) !== false || 
                                   stripos($vetBio, $searchTerm) !== false;
                    $this->assertTrue(
                        $matchesSearch,
                        "Result should match search term '{$searchTerm}'"
                    );
                    
                    // Should have the specified specialization
                    $hasSpecialization = false;
                    foreach ($vet['specializations'] as $spec) {
                        if ($spec['id'] === $specialization1->id) {
                            $hasSpecialization = true;
                            break;
                        }
                    }
                    $this->assertTrue(
                        $hasSpecialization,
                        "Result should have the specified specialization"
                    );
                }
                
                // Verify fully matching vet is in results
                $matchingFound = false;
                foreach ($returnedVets as $vet) {
                    if ($vet['id'] === $matchingVet->id) {
                        $matchingFound = true;
                        break;
                    }
                }
                $this->assertTrue(
                    $matchingFound,
                    "Veterinarian matching both criteria should be in results"
                );
                
                // Verify partial match (wrong specialization) is NOT in results
                $partialFound = false;
                foreach ($returnedVets as $vet) {
                    if ($vet['id'] === $partialMatchVet->id) {
                        $partialFound = true;
                        break;
                    }
                }
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
                
                // Search with empty query
                $response = $this->get(route('doctors.search'));
                
                $response->assertStatus(200);
                
                // Get returned veterinarians
                $returnedVets = $response->viewData('page')['props']['veterinarians']['data'];
                
                // Property: All created veterinarians should be in results
                $this->assertGreaterThanOrEqual(
                    $vetCount,
                    count($returnedVets),
                    "Empty search should return at least all created veterinarians"
                );
                
                // Verify each created vet is in results
                foreach ($createdVets as $vetId) {
                    $found = false;
                    foreach ($returnedVets as $returnedVet) {
                        if ($returnedVet['id'] === $vetId) {
                            $found = true;
                            break;
                        }
                    }
                    $this->assertTrue(
                        $found,
                        "Each created veterinarian should be in empty search results"
                    );
                }
            });
    }
}
