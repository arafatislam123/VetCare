<?php

namespace Tests\Unit\Models;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating pet with missing required fields
     * Validates: Requirements 3.1
     */
    public function test_creating_pet_with_missing_name_fails()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Pet::create([
            'owner_id' => $owner->id,
            // 'name' => 'Buddy', // Missing required field
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);
    }

    public function test_creating_pet_with_missing_species_fails()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            // 'species' => 'dog', // Missing required field
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);
    }

    public function test_creating_pet_with_missing_breed_fails()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            // 'breed' => 'Golden Retriever', // Missing required field
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);
    }

    public function test_creating_pet_with_missing_age_fails()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            // 'age' => 3, // Missing required field
            'weight' => 30.5,
            'gender' => 'male',
        ]);
    }

    public function test_creating_pet_with_missing_weight_fails()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            // 'weight' => 30.5, // Missing required field
            'gender' => 'male',
        ]);
    }

    /**
     * Test updating pet with invalid data
     * Validates: Requirements 3.2
     */
    public function test_updating_pet_with_invalid_age_type()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $pet->update([
            'age' => 'invalid_age', // Invalid type
        ]);
    }

    public function test_updating_pet_with_invalid_weight_type()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $pet->update([
            'weight' => 'invalid_weight', // Invalid type
        ]);
    }

    public function test_updating_pet_with_negative_age()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        // Model allows negative age, but controller validation should prevent it
        $pet->update(['age' => -1]);
        
        $this->assertEquals(-1, $pet->fresh()->age);
    }

    public function test_updating_pet_with_negative_weight()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        // Model allows negative weight, but controller validation should prevent it
        $pet->update(['weight' => -10.5]);
        
        $this->assertEquals(-10.5, $pet->fresh()->weight);
    }

    /**
     * Test deleting pet and verifying soft delete
     * Validates: Requirements 3.3
     */
    public function test_deleting_pet_uses_soft_delete()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        $petId = $pet->id;

        // Delete the pet
        $pet->delete();

        // Verify pet is not in normal queries
        $this->assertNull(Pet::find($petId));

        // Verify pet still exists with soft delete
        $deletedPet = Pet::withTrashed()->find($petId);
        $this->assertNotNull($deletedPet);
        $this->assertNotNull($deletedPet->deleted_at);
    }

    public function test_soft_deleted_pet_can_be_restored()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        $petId = $pet->id;

        // Delete the pet
        $pet->delete();

        // Restore the pet
        $deletedPet = Pet::withTrashed()->find($petId);
        $deletedPet->restore();

        // Verify pet is back in normal queries
        $restoredPet = Pet::find($petId);
        $this->assertNotNull($restoredPet);
        $this->assertNull($restoredPet->deleted_at);
    }

    public function test_soft_deleted_pet_preserves_all_data()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
            'medical_notes' => 'Allergic to chicken',
        ]);

        $originalData = $pet->toArray();
        $petId = $pet->id;

        // Delete the pet
        $pet->delete();

        // Verify all data is preserved
        $deletedPet = Pet::withTrashed()->find($petId);
        $this->assertEquals($originalData['name'], $deletedPet->name);
        $this->assertEquals($originalData['species'], $deletedPet->species);
        $this->assertEquals($originalData['breed'], $deletedPet->breed);
        $this->assertEquals($originalData['age'], $deletedPet->age);
        $this->assertEquals($originalData['weight'], $deletedPet->weight);
        $this->assertEquals($originalData['gender'], $deletedPet->gender);
        $this->assertEquals($originalData['medical_notes'], $deletedPet->medical_notes);
    }

    public function test_force_delete_permanently_removes_pet()
    {
        $owner = User::factory()->create(['role' => 'pet_owner']);
        $pet = Pet::create([
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'weight' => 30.5,
            'gender' => 'male',
        ]);

        $petId = $pet->id;

        // Force delete the pet
        $pet->forceDelete();

        // Verify pet is completely removed
        $this->assertNull(Pet::withTrashed()->find($petId));
    }
}
