<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\Specialization;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class VeterinarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_veterinarian_belongs_to_user()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'bio' => 'Experienced veterinarian',
            'consultation_fee' => 500.00,
        ]);

        $this->assertInstanceOf(User::class, $veterinarian->user);
        $this->assertEquals($user->id, $veterinarian->user->id);
    }

    public function test_veterinarian_has_many_specializations()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        $specialization1 = Specialization::create(['name' => 'Surgery']);
        $specialization2 = Specialization::create(['name' => 'Dentistry']);

        $veterinarian->specializations()->attach([$specialization1->id, $specialization2->id]);

        $this->assertCount(2, $veterinarian->specializations);
        $this->assertTrue($veterinarian->specializations->contains($specialization1));
        $this->assertTrue($veterinarian->specializations->contains($specialization2));
    }

    public function test_available_slots_returns_available_slots_within_date_range()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        // Create available slot within range
        $availableSlot = TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHour(),
            'is_available' => true,
            'is_blocked' => false,
        ]);

        // Create unavailable slot
        TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(10),
            'end_time' => Carbon::now()->addDays(10)->addHour(),
            'is_available' => false,
            'is_blocked' => false,
        ]);

        // Create blocked slot
        TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(15),
            'end_time' => Carbon::now()->addDays(15)->addHour(),
            'is_available' => true,
            'is_blocked' => true,
        ]);

        // Create slot outside range
        TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => Carbon::now()->addDays(40),
            'end_time' => Carbon::now()->addDays(40)->addHour(),
            'is_available' => true,
            'is_blocked' => false,
        ]);

        $slots = $veterinarian->availableSlots($startDate, $endDate);

        $this->assertCount(1, $slots);
        $this->assertEquals($availableSlot->id, $slots->first()->id);
    }

    public function test_average_rating_returns_float()
    {
        $user = User::factory()->create(['role' => 'veterinarian']);
        $veterinarian = Veterinarian::create([
            'user_id' => $user->id,
            'license_number' => 'VET12345',
            'experience_years' => 5,
            'consultation_fee' => 500.00,
        ]);

        $rating = $veterinarian->averageRating();

        $this->assertIsFloat($rating);
        $this->assertEquals(0.0, $rating); // Default implementation returns 0.0
    }
}
