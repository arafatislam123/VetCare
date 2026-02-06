<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeletePreservationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 12: Soft delete preservation
     * 
     * **Validates: Requirements 3.3**
     * 
     * For any pet record, when deleted, the record should still exist in the database 
     * with a deleted_at timestamp rather than being permanently removed.
     */
    public function test_pet_soft_delete_preserves_record_in_database(): void
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

                $petId = $pet->id;
                
                // Verify pet exists before deletion
                $this->assertDatabaseHas('pets', [
                    'id' => $petId,
                    'deleted_at' => null,
                ]);

                // Delete the pet (soft delete)
                $pet->delete();

                // Property: Record should still exist in database with deleted_at timestamp
                
                // Verify the record still exists in the database
                $this->assertDatabaseHas('pets', [
                    'id' => $petId,
                    'name' => $name,
                    'species' => $species,
                    'breed' => $breed,
                    'age' => $age,
                ]);
                
                // Verify deleted_at timestamp is set (not null)
                $deletedPet = Pet::withTrashed()->find($petId);
                $this->assertNotNull($deletedPet, "Pet record should still exist in database");
                $this->assertNotNull($deletedPet->deleted_at, "deleted_at timestamp should be set");
                $this->assertTrue($deletedPet->trashed(), "Pet should be marked as trashed");
                
                // Verify the pet is not retrievable through normal queries
                $normalQuery = Pet::find($petId);
                $this->assertNull($normalQuery, "Pet should not be retrievable through normal queries");
                
                // Verify all original data is preserved
                $this->assertEquals($name, $deletedPet->name, "Name should be preserved");
                $this->assertEquals($species, $deletedPet->species, "Species should be preserved");
                $this->assertEquals($breed, $deletedPet->breed, "Breed should be preserved");
                $this->assertEquals($age, $deletedPet->age, "Age should be preserved");
                $this->assertEquals($weight, $deletedPet->weight, "Weight should be preserved");
                $this->assertEquals($owner->id, $deletedPet->owner_id, "Owner ID should be preserved");
                
                // Verify the record was not permanently deleted
                $recordCount = \DB::table('pets')->where('id', $petId)->count();
                $this->assertEquals(1, $recordCount, "Exactly one record should exist in database");
            });
    }

    /**
     * Additional property test: Multiple soft deletes preserve all records
     * 
     * Tests that when multiple pets are soft deleted, all records are preserved.
     */
    public function test_multiple_soft_deletes_preserve_all_records(): void
    {
        $this->forAll(
            Generator\choose(2, 5)
        )
            ->then(function ($petCount) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                $petIds = [];
                
                // Create multiple pets
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
                    $petIds[] = $pet->id;
                }

                // Soft delete all pets
                foreach ($petIds as $petId) {
                    $pet = Pet::find($petId);
                    $pet->delete();
                }

                // Property: All records should still exist in database
                foreach ($petIds as $index => $petId) {
                    // Verify record exists with deleted_at timestamp
                    $deletedPet = Pet::withTrashed()->find($petId);
                    $this->assertNotNull($deletedPet, "Pet {$petId} should still exist in database");
                    $this->assertNotNull($deletedPet->deleted_at, "Pet {$petId} should have deleted_at timestamp");
                    
                    // Verify data is preserved
                    $this->assertEquals('Pet' . $index, $deletedPet->name, "Pet {$petId} name should be preserved");
                    $this->assertEquals($index + 1, $deletedPet->age, "Pet {$petId} age should be preserved");
                }
                
                // Verify total record count in database
                $totalRecords = \DB::table('pets')->whereIn('id', $petIds)->count();
                $this->assertEquals($petCount, $totalRecords, "All {$petCount} records should exist in database");
            });
    }

    /**
     * Additional property test: Soft deleted pets can be restored
     * 
     * Tests that soft deleted pets can be restored and become accessible again.
     */
    public function test_soft_deleted_pets_can_be_restored(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\choose(0, 30)
        )
            ->withMaxSize(100)
            ->then(function ($nameRaw, $age) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                $name = substr(preg_replace('/[^a-zA-Z ]/', '', $nameRaw), 0, 50);
                if (strlen($name) < 2) {
                    $name = 'TestPet';
                }
                
                // Create and soft delete a pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => $name,
                    'species' => 'Cat',
                    'breed' => 'Siamese',
                    'age' => $age,
                    'weight' => 5.0,
                    'medical_notes' => 'Test notes',
                ]);

                $petId = $pet->id;
                $pet->delete();

                // Verify pet is soft deleted
                $this->assertNull(Pet::find($petId), "Pet should not be accessible after deletion");
                $deletedPet = Pet::withTrashed()->find($petId);
                $this->assertNotNull($deletedPet->deleted_at, "Pet should have deleted_at timestamp");

                // Restore the pet
                $deletedPet->restore();

                // Property: After restoration, deleted_at should be null and pet should be accessible
                $restoredPet = Pet::find($petId);
                $this->assertNotNull($restoredPet, "Pet should be accessible after restoration");
                $this->assertNull($restoredPet->deleted_at, "deleted_at should be null after restoration");
                $this->assertFalse($restoredPet->trashed(), "Pet should not be marked as trashed after restoration");
                
                // Verify all data is still preserved
                $this->assertEquals($name, $restoredPet->name, "Name should be preserved after restoration");
                $this->assertEquals($age, $restoredPet->age, "Age should be preserved after restoration");
                $this->assertEquals($owner->id, $restoredPet->owner_id, "Owner ID should be preserved after restoration");
            });
    }
}
