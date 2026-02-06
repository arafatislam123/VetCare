<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Veterinarian;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotDateRangePropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 9: Time slot date range
     * 
     * **Validates: Requirements 2.4**
     * 
     * For any doctor profile view, the displayed time slots should all fall 
     * within the next 30 days from the current date.
     */
    public function test_time_slots_fall_within_30_day_range(): void
    {
        $this->forAll(
            Generator\choose(1, 10), // Number of time slots to create
            Generator\choose(0, 29),  // Days offset within valid range
            Generator\choose(31, 60)  // Days offset outside valid range
        )
            ->withMaxSize(100)
            ->then(function ($slotCount, $validDayOffset, $invalidDayOffset) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                TimeSlot::query()->delete();
                
                // Create a veterinarian
                $user = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. Test Veterinarian',
                    'email' => uniqid() . '@example.com',
                ]);
                
                $veterinarian = Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'General veterinary practice',
                    'consultation_fee' => 500.00,
                ]);
                
                // Define the date range (next 30 days)
                $startDate = now();
                $endDate = now()->addDays(30);
                
                // Create time slots within the valid range (0-29 days from now)
                $validSlots = [];
                for ($i = 0; $i < $slotCount; $i++) {
                    $slotStartTime = now()->addDays($validDayOffset)->addHours($i);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    $slot = TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => false,
                    ]);
                    $validSlots[] = $slot->id;
                }
                
                // Create time slots outside the valid range (31-60 days from now)
                $invalidSlots = [];
                for ($i = 0; $i < 2; $i++) {
                    $slotStartTime = now()->addDays($invalidDayOffset)->addHours($i);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    $slot = TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => false,
                    ]);
                    $invalidSlots[] = $slot->id;
                }
                
                // Get available slots using the veterinarian's method
                $availableSlots = $veterinarian->availableSlots($startDate, $endDate);
                
                // Property 1: All returned slots should fall within the 30-day range
                foreach ($availableSlots as $slot) {
                    $slotStartTime = Carbon::parse($slot->start_time);
                    
                    $this->assertGreaterThanOrEqual(
                        $startDate->timestamp,
                        $slotStartTime->timestamp,
                        "Time slot start time should be on or after the start date. " .
                        "Slot: {$slotStartTime->toDateTimeString()}, Start: {$startDate->toDateTimeString()}"
                    );
                    
                    $this->assertLessThanOrEqual(
                        $endDate->timestamp,
                        $slotStartTime->timestamp,
                        "Time slot start time should be on or before the end date (30 days from now). " .
                        "Slot: {$slotStartTime->toDateTimeString()}, End: {$endDate->toDateTimeString()}"
                    );
                }
                
                // Property 2: All valid slots should be included in results
                foreach ($validSlots as $validSlotId) {
                    $found = $availableSlots->contains('id', $validSlotId);
                    $this->assertTrue(
                        $found,
                        "Time slot within 30-day range should be included in results"
                    );
                }
                
                // Property 3: No invalid slots (outside 30-day range) should be included
                foreach ($invalidSlots as $invalidSlotId) {
                    $found = $availableSlots->contains('id', $invalidSlotId);
                    $this->assertFalse(
                        $found,
                        "Time slot outside 30-day range should NOT be included in results"
                    );
                }
                
                // Property 4: Result count should match valid slots count
                $this->assertEquals(
                    $slotCount,
                    $availableSlots->count(),
                    "Number of returned slots should equal the number of valid slots created"
                );
            });
    }

    /**
     * Property test: Time slots are ordered by start time
     * 
     * Verifies that returned time slots are sorted chronologically.
     */
    public function test_time_slots_are_ordered_by_start_time(): void
    {
        $this->forAll(
            Generator\choose(3, 8) // Number of time slots to create
        )
            ->then(function ($slotCount) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                TimeSlot::query()->delete();
                
                // Create a veterinarian
                $user = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. Test Veterinarian',
                    'email' => uniqid() . '@example.com',
                ]);
                
                $veterinarian = Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'General veterinary practice',
                    'consultation_fee' => 500.00,
                ]);
                
                // Create time slots in random order within the 30-day range
                $randomDays = [];
                for ($i = 0; $i < $slotCount; $i++) {
                    $randomDays[] = rand(0, 29);
                }
                
                foreach ($randomDays as $dayOffset) {
                    $slotStartTime = now()->addDays($dayOffset)->addHours(rand(8, 17));
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => false,
                    ]);
                }
                
                // Get available slots
                $startDate = now();
                $endDate = now()->addDays(30);
                $availableSlots = $veterinarian->availableSlots($startDate, $endDate);
                
                // Property: Slots should be ordered by start_time (ascending)
                $previousTimestamp = null;
                foreach ($availableSlots as $slot) {
                    $currentTimestamp = Carbon::parse($slot->start_time)->timestamp;
                    
                    if ($previousTimestamp !== null) {
                        $this->assertGreaterThanOrEqual(
                            $previousTimestamp,
                            $currentTimestamp,
                            "Time slots should be ordered chronologically by start_time"
                        );
                    }
                    
                    $previousTimestamp = $currentTimestamp;
                }
            });
    }

    /**
     * Property test: Only available and non-blocked slots are returned
     * 
     * Verifies that unavailable or blocked slots are excluded from results.
     */
    public function test_only_available_non_blocked_slots_returned(): void
    {
        $this->forAll(
            Generator\choose(2, 6) // Number of each type of slot
        )
            ->then(function ($slotCount) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                TimeSlot::query()->delete();
                
                // Create a veterinarian
                $user = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. Test Veterinarian',
                    'email' => uniqid() . '@example.com',
                ]);
                
                $veterinarian = Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'General veterinary practice',
                    'consultation_fee' => 500.00,
                ]);
                
                $availableSlotIds = [];
                $unavailableSlotIds = [];
                $blockedSlotIds = [];
                
                // Create available slots (should be returned)
                for ($i = 0; $i < $slotCount; $i++) {
                    $slotStartTime = now()->addDays($i)->addHours(9);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    $slot = TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => false,
                    ]);
                    $availableSlotIds[] = $slot->id;
                }
                
                // Create unavailable slots (should NOT be returned)
                for ($i = 0; $i < $slotCount; $i++) {
                    $slotStartTime = now()->addDays($i)->addHours(11);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    $slot = TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => false,
                        'is_blocked' => false,
                    ]);
                    $unavailableSlotIds[] = $slot->id;
                }
                
                // Create blocked slots (should NOT be returned)
                for ($i = 0; $i < $slotCount; $i++) {
                    $slotStartTime = now()->addDays($i)->addHours(13);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    $slot = TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => true,
                    ]);
                    $blockedSlotIds[] = $slot->id;
                }
                
                // Get available slots
                $startDate = now();
                $endDate = now()->addDays(30);
                $availableSlots = $veterinarian->availableSlots($startDate, $endDate);
                
                // Property 1: All available slots should be in results
                foreach ($availableSlotIds as $slotId) {
                    $found = $availableSlots->contains('id', $slotId);
                    $this->assertTrue(
                        $found,
                        "Available and non-blocked slot should be in results"
                    );
                }
                
                // Property 2: No unavailable slots should be in results
                foreach ($unavailableSlotIds as $slotId) {
                    $found = $availableSlots->contains('id', $slotId);
                    $this->assertFalse(
                        $found,
                        "Unavailable slot should NOT be in results"
                    );
                }
                
                // Property 3: No blocked slots should be in results
                foreach ($blockedSlotIds as $slotId) {
                    $found = $availableSlots->contains('id', $slotId);
                    $this->assertFalse(
                        $found,
                        "Blocked slot should NOT be in results"
                    );
                }
                
                // Property 4: Result count should match available slot count
                $this->assertEquals(
                    $slotCount,
                    $availableSlots->count(),
                    "Only available and non-blocked slots should be returned"
                );
            });
    }

    /**
     * Property test: Empty result when no slots in date range
     * 
     * Verifies that an empty collection is returned when no slots exist in the range.
     */
    public function test_empty_result_when_no_slots_in_range(): void
    {
        $this->forAll(
            Generator\choose(1, 5) // Number of slots to create outside range
        )
            ->then(function ($slotCount) {
                // Clear database for each iteration
                User::query()->delete();
                Veterinarian::query()->delete();
                TimeSlot::query()->delete();
                
                // Create a veterinarian
                $user = User::factory()->create([
                    'role' => 'veterinarian',
                    'name' => 'Dr. Test Veterinarian',
                    'email' => uniqid() . '@example.com',
                ]);
                
                $veterinarian = Veterinarian::create([
                    'user_id' => $user->id,
                    'license_number' => 'VET' . uniqid(),
                    'experience_years' => 5,
                    'bio' => 'General veterinary practice',
                    'consultation_fee' => 500.00,
                ]);
                
                // Create time slots OUTSIDE the 30-day range (in the past)
                for ($i = 0; $i < $slotCount; $i++) {
                    $slotStartTime = now()->subDays(rand(1, 10))->addHours($i);
                    $slotEndTime = $slotStartTime->copy()->addHour();
                    
                    TimeSlot::create([
                        'veterinarian_id' => $veterinarian->id,
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'is_available' => true,
                        'is_blocked' => false,
                    ]);
                }
                
                // Get available slots for next 30 days
                $startDate = now();
                $endDate = now()->addDays(30);
                $availableSlots = $veterinarian->availableSlots($startDate, $endDate);
                
                // Property: Should return empty collection when no slots in range
                $this->assertEmpty(
                    $availableSlots,
                    "Should return empty collection when no time slots exist in the date range"
                );
                
                $this->assertEquals(
                    0,
                    $availableSlots->count(),
                    "Slot count should be zero when no slots in range"
                );
            });
    }
}

