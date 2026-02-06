<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\Specialization;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_veterinarians()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        $response = $this->get(route('doctors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Index')
            ->has('veterinarians.data', 1)
        );
    }

    public function test_index_filters_by_specialization()
    {
        $specialization = Specialization::create(['name' => 'Surgery']);

        $user1 = User::factory()->create(['role' => 'veterinarian']);
        $vet1 = Veterinarian::create([
            'user_id' => $user1->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);
        $vet1->specializations()->attach($specialization->id);

        $user2 = User::factory()->create(['role' => 'veterinarian']);
        $vet2 = Veterinarian::create([
            'user_id' => $user2->id,
            'license_number' => 'VET67890',
            'experience_years' => 8,
            'consultation_fee' => 600.00,
        ]);

        $response = $this->get(route('doctors.index', ['specialization' => $specialization->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Index')
            ->has('veterinarians.data', 1)
        );
    }

    public function test_show_displays_veterinarian_profile()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'bio' => 'Experienced veterinarian',
            'consultation_fee' => 500.00,
        ]);

        $response = $this->get(route('doctors.show', $veterinarian));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Show')
            ->has('veterinarian')
            ->has('averageRating')
            ->has('availableSlots')
        );
    }

    public function test_show_includes_available_slots_for_next_30_days()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        // Create slot within 30 days
        TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHour(),
            'is_available' => true,
            'is_blocked' => false,
        ]);

        // Create slot outside 30 days
        TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(40),
            'end_time' => Carbon::now()->addDays(40)->addHour(),
            'is_available' => true,
            'is_blocked' => false,
        ]);

        $response = $this->get(route('doctors.show', $veterinarian));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Show')
            ->where('availableSlots', fn ($slots) => count($slots) === 1)
        );
    }

    public function test_search_returns_matching_veterinarians()
    {
        $user = User::factory()->create([
            'role' => 'veterinarian',
            'name' => 'Dr. John Smith',
        ]);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'bio' => 'Specializes in exotic animals',
            'consultation_fee' => 500.00,
        ]);

        $response = $this->get(route('doctors.search', ['query' => 'John']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Search')
            ->has('veterinarians.data', 1)
        );
    }

    public function test_search_filters_by_specialization()
    {
        $specialization = Specialization::create(['name' => 'Surgery']);

        $user1 = User::factory()->create(['role' => 'veterinarian']);
        $vet1 = Veterinarian::create([
            'user_id' => $user1->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);
        $vet1->specializations()->attach($specialization->id);

        $user2 = User::factory()->create(['role' => 'veterinarian']);
        $vet2 = Veterinarian::create([
            'user_id' => $user2->id,
            'license_number' => 'VET67890',
            'experience_years' => 8,
            'consultation_fee' => 600.00,
        ]);

        $response = $this->get(route('doctors.search', ['specialization' => $specialization->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Doctors/Search')
            ->has('veterinarians.data', 1)
        );
    }
}
