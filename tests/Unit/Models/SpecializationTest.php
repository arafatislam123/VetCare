<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_specialization_has_many_veterinarians()
    {
        $specialization = Specialization::create([
            'name' => 'Surgery',
            'description' => 'Surgical procedures for animals',
        ]);

        $user1 = User::factory()->create(['role' => 'veterinarian']);
        $vet1 = Veterinarian::create([
            'user_id' => $user1->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        $user2 = User::factory()->create(['role' => 'veterinarian']);
        $vet2 = Veterinarian::create([
            'user_id' => $user2->id,
            'license_number' => 'VET67890',
            'experience_years' => 8,
            'consultation_fee' => 600.00,
        ]);

        $specialization->veterinarians()->attach([$vet1->id, $vet2->id]);

        $this->assertCount(2, $specialization->veterinarians);
        $this->assertTrue($specialization->veterinarians->contains($vet1));
        $this->assertTrue($specialization->veterinarians->contains($vet2));
    }

    public function test_specialization_can_be_created_with_name_and_description()
    {
        $specialization = Specialization::create([
            'name' => 'Dentistry',
            'description' => 'Dental care for pets',
        ]);

        $this->assertDatabaseHas('specializations', [
            'name' => 'Dentistry',
            'description' => 'Dental care for pets',
        ]);
    }
}
