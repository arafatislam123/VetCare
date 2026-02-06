<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetOwnershipUniquenessPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 13: Pet ownership uniqueness
     * 
     * **Validates: Requirements 3.4**
     * 
     * For any pet record in the database, it should be associated with exactly one pet owner.
     */
    public function test_pet_is_associated_with_exactly_one_owner(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\string(),
            Generator\choose(0, 30),
            Generator\choose(1, 200)
        )
            ->withMaxSize(100)
            ->then(function ($nameRaw, $speciesRaw, $breedRaw, $age, $weightInt) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Generate valid pet data
                $name = substr(preg_replace('/[^a-zA-Z ]/', '', $nameRaw), 0, 50);
                if (strlen($name) < 2) {
                    $name = 'TestPet';
                }
                
                $species = substr(preg_replace('/[^a-zA-Z ]/', '', $speciesRaw), 0, 50);
                if (strlen($species) < 2) {
                    $species = 'Dog';
                }
                
                $breed = substr(preg_replace('/[^a-zA-Z ]/', '', $breedRaw), 0, 50);
                if (strlen($breed) < 2) {
                    $breed = 'Mixed';
                }
                
                $weight = $weightInt / 10.0;
                if ($weight < 0.1) {
                    $weight = 1.0;
                }
                
                // Create pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                    'weight' => $weight,
                    'medical_notes' => 'Test medical notes',
                ]);

                // Property: Pet should be associated with exactly one owner
                
                // Verify pet has an owner_id
                $this->assertNotNull($pet->owner_id, "Pet should have an owner_id");
                
                // Verify the owner_id matches the created owner
                $this->assertEquals($owner->id, $pet->owner_id, "Pet owner_id should match the created owner");
                
                // Verify the pet can access its owner through the relationship
                $petOwner = $pet->owner;
                $this->assertNotNull($petOwner, "Pet should have an accessible owner relationship");
                $this->assertInstanceOf(User::class, $petOwner, "Pet owner should be a User instance");
                
                // Verify it's the same owner (exactly one)
                $this->assertEquals($owner->id, $petOwner->id, "Pet owner relationship should point to exactly one owner");
                $this->assertEquals($owner->email, $petOwner->email, "Pet owner email should match");
                $this->assertEquals($owner->name, $petOwner->name, "Pet owner name should match");
                
                // Verify the owner can access this pet through their pets relationship
                $ownerPets = $owner->pets;
                $this->assertTrue($ownerPets->contains($pet), "Owner should have this pet in their pets collection");
                
                // Verify the pet record in database has exactly one owner_id (not null, not multiple)
                $petFromDb = Pet::find($pet->id);
                $this->assertNotNull($petFromDb->owner_id, "Pet in database should have an owner_id");
                $this->assertEquals($owner->id, $petFromDb->owner_id, "Pet in database should have exactly one owner_id");
                
                // Verify the owner_id is a valid foreign key pointing to an existing user
                $ownerExists = User::where('id', $petFromDb->owner_id)->exists();
                $this->assertTrue($ownerExists, "Pet owner_id should reference an existing user");
            });
    }

    /**
     * Additional property test: Pet cannot exist without an owner
     * 
     * Tests that the owner_id is required and cannot be null.
     */
    public function test_pet_cannot_be_created_without_owner(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Attempt to create a pet without an owner_id
        Pet::create([
            'name' => 'Orphan Pet',
            'species' => 'Dog',
            'breed' => 'Mixed',
            'age' => 5,
            'weight' => 10.0,
            'medical_notes' => 'No owner',
        ]);
    }

    /**
     * Additional property test: Multiple pets can belong to the same owner
     * 
     * Tests that one owner can have multiple pets, but each pet has exactly one owner.
     */
    public function test_multiple_pets_can_belong_to_same_owner_but_each_has_one_owner(): void
    {
        $this->forAll(
            Generator\choose(2, 5)
        )
            ->then(function ($petCount) {
                // Create a single pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                $pets = [];
                
                // Create multiple pets for the same owner
                for ($i = 0; $i < $petCount; $i++) {
                    $pet = Pet::create([
                        'owner_id' => $owner->id,
                        'name' => 'Pet' . $i,
                        'species' => 'Dog',
                        'breed' => 'Mixed',
                        'age' => $i + 1,
                        'weight' => 10.0 + $i,
                        'medical_notes' => 'Notes for pet ' . $i,
                    ]);
                    $pets[] = $pet;
                }

                // Property: Each pet should have exactly one owner (the same owner)
                foreach ($pets as $pet) {
                    // Verify each pet has exactly one owner_id
                    $this->assertNotNull($pet->owner_id, "Pet should have an owner_id");
                    $this->assertEquals($owner->id, $pet->owner_id, "Pet should belong to the created owner");
                    
                    // Verify each pet's owner relationship points to exactly one owner
                    $petOwner = $pet->owner;
                    $this->assertNotNull($petOwner, "Pet should have an owner relationship");
                    $this->assertEquals($owner->id, $petOwner->id, "Pet owner should be the same owner");
                }
                
                // Verify the owner has all pets in their collection
                $ownerPets = $owner->fresh()->pets;
                $this->assertCount($petCount, $ownerPets, "Owner should have all {$petCount} pets");
                
                // Verify each pet in the collection has the same owner
                foreach ($ownerPets as $ownerPet) {
                    $this->assertEquals($owner->id, $ownerPet->owner_id, "Each pet should have the same owner_id");
                }
            });
    }

    /**
     * Additional property test: Pet ownership cannot be changed to null
     * 
     * Tests that once a pet has an owner, the owner_id cannot be set to null.
     */
    public function test_pet_ownership_cannot_be_changed_to_null(): void
    {
        // Create a pet owner
        $owner = User::create([
            'name' => 'Test Owner',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pet_owner',
        ]);

        // Create a pet
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'TestPet',
            'species' => 'Cat',
            'breed' => 'Persian',
            'age' => 3,
            'weight' => 4.5,
            'medical_notes' => 'Healthy',
        ]);

        // Verify pet has an owner
        $this->assertEquals($owner->id, $pet->owner_id);

        // Attempt to set owner_id to null should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $pet->owner_id = null;
        $pet->save();
    }

    /**
     * Additional property test: Pet ownership can be transferred to another owner
     * 
     * Tests that a pet can be transferred from one owner to another, 
     * but always has exactly one owner at any time.
     */
    public function test_pet_ownership_can_be_transferred_but_always_has_one_owner(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($nameRaw) {
                // Create first owner
                $owner1 = User::create([
                    'name' => 'First Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create second owner
                $owner2 = User::create([
                    'name' => 'Second Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                $name = substr(preg_replace('/[^a-zA-Z ]/', '', $nameRaw), 0, 50);
                if (strlen($name) < 2) {
                    $name = 'TestPet';
                }

                // Create pet with first owner
                $pet = Pet::create([
                    'owner_id' => $owner1->id,
                    'name' => $name,
                    'species' => 'Dog',
                    'breed' => 'Labrador',
                    'age' => 5,
                    'weight' => 25.0,
                    'medical_notes' => 'Healthy dog',
                ]);

                // Verify pet belongs to first owner
                $this->assertEquals($owner1->id, $pet->owner_id, "Pet should initially belong to first owner");
                $this->assertEquals($owner1->id, $pet->owner->id, "Pet owner relationship should point to first owner");

                // Transfer pet to second owner
                $pet->owner_id = $owner2->id;
                $pet->save();

                // Refresh the pet from database
                $pet->refresh();

                // Property: Pet should now have exactly one owner (the second owner)
                $this->assertNotNull($pet->owner_id, "Pet should still have an owner_id after transfer");
                $this->assertEquals($owner2->id, $pet->owner_id, "Pet should now belong to second owner");
                $this->assertEquals($owner2->id, $pet->owner->id, "Pet owner relationship should point to second owner");
                
                // Verify pet is no longer associated with first owner
                $this->assertNotEquals($owner1->id, $pet->owner_id, "Pet should no longer belong to first owner");
                
                // Verify the pet appears in second owner's pets collection
                $owner2Pets = $owner2->fresh()->pets;
                $this->assertTrue($owner2Pets->contains($pet), "Second owner should have this pet");
                
                // Verify the pet does not appear in first owner's pets collection
                $owner1Pets = $owner1->fresh()->pets;
                $this->assertFalse($owner1Pets->contains($pet), "First owner should not have this pet anymore");
            });
    }
}
