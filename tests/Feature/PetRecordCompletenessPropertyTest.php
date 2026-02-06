<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetRecordCompletenessPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 10: Pet record completeness
     * 
     * **Validates: Requirements 3.1**
     * 
     * For any pet creation request, when the pet is stored, all required fields 
     * (species, breed, age, weight, medical history) should be retrievable from the database.
     */
    public function test_pet_record_stores_all_required_fields(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string(),
            Generator\string(),
            Generator\choose(0, 30),
            Generator\choose(1, 200),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($nameRaw, $speciesRaw, $breedRaw, $age, $weightInt, $medicalNotesRaw) {
                // Create a pet owner for the test
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
                
                // Weight as decimal (convert integer to decimal with 2 places)
                $weight = $weightInt / 10.0; // Creates values like 0.1 to 20.0
                if ($weight < 0.1) {
                    $weight = 1.0;
                }
                
                $medicalNotes = substr($medicalNotesRaw, 0, 500);
                if (strlen($medicalNotes) < 5) {
                    $medicalNotes = 'No known medical issues';
                }
                
                // Create pet with all required fields
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                    'weight' => $weight,
                    'medical_notes' => $medicalNotes,
                ]);

                // Verify the pet was created in the database
                $this->assertDatabaseHas('pets', [
                    'id' => $pet->id,
                    'owner_id' => $owner->id,
                ]);

                // Retrieve the pet from the database
                $retrievedPet = Pet::find($pet->id);
                
                // Property: All required fields should be retrievable
                $this->assertNotNull($retrievedPet, "Pet should exist in database");
                
                // Verify species is stored and retrievable
                $this->assertNotNull($retrievedPet->species, "Species should be stored");
                $this->assertEquals($species, $retrievedPet->species, "Species should match");
                
                // Verify breed is stored and retrievable
                $this->assertNotNull($retrievedPet->breed, "Breed should be stored");
                $this->assertEquals($breed, $retrievedPet->breed, "Breed should match");
                
                // Verify age is stored and retrievable
                $this->assertNotNull($retrievedPet->age, "Age should be stored");
                $this->assertEquals($age, $retrievedPet->age, "Age should match");
                $this->assertIsInt($retrievedPet->age, "Age should be an integer");
                
                // Verify weight is stored and retrievable
                $this->assertNotNull($retrievedPet->weight, "Weight should be stored");
                $this->assertEquals($weight, $retrievedPet->weight, "Weight should match");
                $this->assertIsFloat($retrievedPet->weight, "Weight should be a float");
                
                // Verify medical history is stored and retrievable
                $this->assertNotNull($retrievedPet->medical_notes, "Medical notes should be stored");
                $this->assertEquals($medicalNotes, $retrievedPet->medical_notes, "Medical notes should match");
                
                // Verify name is also stored (additional field)
                $this->assertNotNull($retrievedPet->name, "Name should be stored");
                $this->assertEquals($name, $retrievedPet->name, "Name should match");
                
                // Verify owner relationship is maintained
                $this->assertEquals($owner->id, $retrievedPet->owner_id, "Owner ID should match");
                $this->assertNotNull($retrievedPet->owner, "Owner relationship should be accessible");
                $this->assertEquals($owner->id, $retrievedPet->owner->id, "Owner relationship should be correct");
            });
    }

    /**
     * Additional property test: Pet record completeness with optional fields
     * 
     * Tests that optional fields (gender) can be stored and retrieved when provided.
     */
    public function test_pet_record_stores_optional_fields(): void
    {
        $this->forAll(
            Generator\elements('Male', 'Female', 'Unknown')
        )
            ->then(function ($gender) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create pet with optional gender field
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => 'TestPet',
                    'species' => 'Cat',
                    'breed' => 'Persian',
                    'age' => 3,
                    'weight' => 4.5,
                    'gender' => $gender,
                    'medical_notes' => 'Healthy',
                ]);

                // Retrieve the pet
                $retrievedPet = Pet::find($pet->id);
                
                // Verify optional field is stored and retrievable
                $this->assertNotNull($retrievedPet->gender, "Gender should be stored when provided");
                $this->assertEquals($gender, $retrievedPet->gender, "Gender should match");
            });
    }

    /**
     * Additional property test: Pet record without optional fields
     * 
     * Tests that pets can be created without optional fields.
     */
    public function test_pet_record_can_be_created_without_optional_fields(): void
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
                
                // Create pet WITHOUT optional fields (gender, medical_notes)
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                    'weight' => $weight,
                ]);

                // Retrieve the pet
                $retrievedPet = Pet::find($pet->id);
                
                // Verify required fields are still stored
                $this->assertNotNull($retrievedPet, "Pet should exist in database");
                $this->assertEquals($species, $retrievedPet->species, "Species should match");
                $this->assertEquals($breed, $retrievedPet->breed, "Breed should match");
                $this->assertEquals($age, $retrievedPet->age, "Age should match");
                $this->assertEquals($weight, $retrievedPet->weight, "Weight should match");
                
                // Verify optional fields are null when not provided
                $this->assertNull($retrievedPet->gender, "Gender should be null when not provided");
            });
    }
}
