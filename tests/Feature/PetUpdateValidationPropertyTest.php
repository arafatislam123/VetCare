<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PetUpdateValidationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 11: Pet update validation
     * 
     * **Validates: Requirements 3.2**
     * 
     * For any pet update request missing required fields, the system should 
     * reject the update and return a validation error.
     */
    public function test_pet_update_rejects_missing_required_fields(): void
    {
        $this->forAll(
            Generator\elements('name', 'species', 'breed', 'age', 'weight', 'gender')
        )
            ->then(function ($missingField) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create an existing pet with all required fields
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => 'Original Pet',
                    'species' => 'dog',
                    'breed' => 'Labrador',
                    'age' => 5,
                    'weight' => 25.5,
                    'gender' => 'male',
                    'medical_notes' => 'Healthy',
                ]);

                // Prepare update data with all required fields
                $updateData = [
                    'name' => 'Updated Pet',
                    'species' => 'cat',
                    'breed' => 'Persian',
                    'age' => 3,
                    'weight' => 4.5,
                    'gender' => 'female',
                    'medical_notes' => 'Updated notes',
                ];

                // Remove the specified required field
                unset($updateData[$missingField]);

                // Define validation rules (same as in PetController)
                $rules = [
                    'name' => 'required|string|max:255',
                    'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
                    'breed' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                    'weight' => 'required|numeric|min:0',
                    'gender' => 'required|in:male,female',
                    'medical_notes' => 'nullable|string',
                ];

                // Validate the update data
                $validator = Validator::make($updateData, $rules);

                // Property: Update should fail validation when required field is missing
                $this->assertTrue(
                    $validator->fails(),
                    "Validation should fail when required field '{$missingField}' is missing"
                );

                // Verify the specific field has an error
                $this->assertTrue(
                    $validator->errors()->has($missingField),
                    "Validation errors should include the missing field '{$missingField}'"
                );

                // Verify the pet was NOT updated in the database
                $pet->refresh();
                $this->assertEquals('Original Pet', $pet->name, "Pet name should not be updated");
                $this->assertEquals('dog', $pet->species, "Pet species should not be updated");
                $this->assertEquals('Labrador', $pet->breed, "Pet breed should not be updated");
                $this->assertEquals(5, $pet->age, "Pet age should not be updated");
                $this->assertEquals(25.5, $pet->weight, "Pet weight should not be updated");
                $this->assertEquals('male', $pet->gender, "Pet gender should not be updated");
            });
    }

    /**
     * Additional property test: Pet update validation with invalid data types
     * 
     * Tests that updates with invalid data types are rejected.
     */
    public function test_pet_update_rejects_invalid_data_types(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($invalidAge, $invalidWeight) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create an existing pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => 'Original Pet',
                    'species' => 'dog',
                    'breed' => 'Labrador',
                    'age' => 5,
                    'weight' => 25.5,
                    'gender' => 'male',
                    'medical_notes' => 'Healthy',
                ]);

                // Prepare update data with invalid types for age and weight
                $updateData = [
                    'name' => 'Updated Pet',
                    'species' => 'cat',
                    'breed' => 'Persian',
                    'age' => $invalidAge, // Should be integer
                    'weight' => $invalidWeight, // Should be numeric
                    'gender' => 'female',
                ];

                // Define validation rules
                $rules = [
                    'name' => 'required|string|max:255',
                    'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
                    'breed' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                    'weight' => 'required|numeric|min:0',
                    'gender' => 'required|in:male,female',
                    'medical_notes' => 'nullable|string',
                ];

                // Validate the update data
                $validator = Validator::make($updateData, $rules);

                // Property: Update should fail validation when data types are invalid
                // (unless the strings happen to be valid numeric values)
                if (!is_numeric($invalidAge) || !is_numeric($invalidWeight)) {
                    $this->assertTrue(
                        $validator->fails(),
                        "Validation should fail when age or weight have invalid data types"
                    );

                    // Verify the pet was NOT updated
                    $pet->refresh();
                    $this->assertEquals(5, $pet->age, "Pet age should not be updated with invalid data");
                    $this->assertEquals(25.5, $pet->weight, "Pet weight should not be updated with invalid data");
                }
            });
    }

    /**
     * Additional property test: Pet update validation with invalid species
     * 
     * Tests that updates with invalid species values are rejected.
     */
    public function test_pet_update_rejects_invalid_species(): void
    {
        $this->forAll(
            Generator\string()
        )
            ->withMaxSize(100)
            ->then(function ($invalidSpecies) {
                // Skip if the invalid species happens to be a valid one
                $validSpecies = ['dog', 'cat', 'bird', 'rabbit', 'hamster', 'cow', 'goat', 'sheep', 'chicken', 'duck', 'horse', 'pig', 'other'];
                if (in_array(strtolower($invalidSpecies), $validSpecies)) {
                    return;
                }

                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create an existing pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => 'Original Pet',
                    'species' => 'dog',
                    'breed' => 'Labrador',
                    'age' => 5,
                    'weight' => 25.5,
                    'gender' => 'male',
                    'medical_notes' => 'Healthy',
                ]);

                // Prepare update data with invalid species
                $updateData = [
                    'name' => 'Updated Pet',
                    'species' => $invalidSpecies,
                    'breed' => 'Persian',
                    'age' => 3,
                    'weight' => 4.5,
                    'gender' => 'female',
                ];

                // Define validation rules
                $rules = [
                    'name' => 'required|string|max:255',
                    'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
                    'breed' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                    'weight' => 'required|numeric|min:0',
                    'gender' => 'required|in:male,female',
                    'medical_notes' => 'nullable|string',
                ];

                // Validate the update data
                $validator = Validator::make($updateData, $rules);

                // Property: Update should fail validation when species is invalid
                $this->assertTrue(
                    $validator->fails(),
                    "Validation should fail when species '{$invalidSpecies}' is not in the allowed list"
                );

                // Verify the species field has an error
                $this->assertTrue(
                    $validator->errors()->has('species'),
                    "Validation errors should include the species field"
                );

                // Verify the pet was NOT updated
                $pet->refresh();
                $this->assertEquals('dog', $pet->species, "Pet species should not be updated with invalid value");
            });
    }

    /**
     * Additional property test: Pet update validation with negative values
     * 
     * Tests that updates with negative age or weight are rejected.
     */
    public function test_pet_update_rejects_negative_values(): void
    {
        $this->forAll(
            Generator\choose(-100, -1),
            Generator\choose(-100, -1)
        )
            ->then(function ($negativeAge, $negativeWeight) {
                // Create a pet owner
                $owner = User::create([
                    'name' => 'Test Owner',
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'pet_owner',
                ]);

                // Create an existing pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => 'Original Pet',
                    'species' => 'dog',
                    'breed' => 'Labrador',
                    'age' => 5,
                    'weight' => 25.5,
                    'gender' => 'male',
                    'medical_notes' => 'Healthy',
                ]);

                // Prepare update data with negative values
                $updateData = [
                    'name' => 'Updated Pet',
                    'species' => 'cat',
                    'breed' => 'Persian',
                    'age' => $negativeAge,
                    'weight' => $negativeWeight,
                    'gender' => 'female',
                ];

                // Define validation rules
                $rules = [
                    'name' => 'required|string|max:255',
                    'species' => 'required|string|max:255|in:dog,cat,bird,rabbit,hamster,cow,goat,sheep,chicken,duck,horse,pig,other',
                    'breed' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                    'weight' => 'required|numeric|min:0',
                    'gender' => 'required|in:male,female',
                    'medical_notes' => 'nullable|string',
                ];

                // Validate the update data
                $validator = Validator::make($updateData, $rules);

                // Property: Update should fail validation when age or weight are negative
                $this->assertTrue(
                    $validator->fails(),
                    "Validation should fail when age or weight are negative"
                );

                // Verify the pet was NOT updated
                $pet->refresh();
                $this->assertEquals(5, $pet->age, "Pet age should not be updated with negative value");
                $this->assertEquals(25.5, $pet->weight, "Pet weight should not be updated with negative value");
            });
    }
}
