<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Pet;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\Veterinarian;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PaymentDataEncryptionPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 23: Payment data encryption
     * 
     * **Validates: Requirements 5.5**
     * 
     * For any payment record, sensitive fields (transaction_id, gateway_response) 
     * should be encrypted in the database.
     */
    public function test_payment_gateway_response_is_encrypted(): void
    {
        $this->forAll(
            Generator\names(),
            Generator\elements('bkash', 'nagad'),
            Generator\choose(100, 100000)
        )
            ->withMaxSize(100)
            ->then(function ($gatewayResponseBase, $gateway, $amountInCents) {
                // Generate a realistic gateway response
                $gatewayResponse = 'gateway_response_' . $gatewayResponseBase . '_' . uniqid();
                
                $amount = $amountInCents / 100;
                $serviceCharge = 50.00;
                $totalAmount = $amount + $serviceCharge;
                
                // Create a pet owner
                $petOwner = User::factory()->create(['role' => 'pet_owner']);
                
                // Create a veterinarian
                $veterinarian = Veterinarian::factory()->create();
                
                // Create a pet
                $pet = Pet::create([
                    'owner_id' => $petOwner->id,
                    'name' => 'Test Pet',
                    'species' => 'dog',
                    'breed' => 'Labrador',
                    'age' => 3,
                    'weight' => 25.5,
                    'gender' => 'male',
                ]);
                
                // Create a time slot
                $timeSlot = TimeSlot::create([
                    'veterinarian_id' => $veterinarian->id,
                    'start_time' => now()->addDay(),
                    'end_time' => now()->addDay()->addHour(),
                    'is_available' => false,
                    'is_blocked' => false,
                ]);
                
                // Create an appointment
                $appointment = Appointment::create([
                    'pet_owner_id' => $petOwner->id,
                    'veterinarian_id' => $veterinarian->id,
                    'pet_id' => $pet->id,
                    'time_slot_id' => $timeSlot->id,
                    'status' => 'pending',
                ]);
                
                // Create payment with gateway response
                $payment = Payment::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $petOwner->id,
                    'gateway' => $gateway,
                    'transaction_id' => 'TXN_' . uniqid(),
                    'amount' => $amount,
                    'service_charge' => $serviceCharge,
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'gateway_response' => $gatewayResponse,
                ]);

                // Retrieve the payment from the database
                $retrievedPayment = Payment::find($payment->id);
                
                // Property 1: Gateway response should be accessible (decrypted automatically)
                $this->assertEquals(
                    $gatewayResponse,
                    $retrievedPayment->gateway_response,
                    "Gateway response should be decrypted automatically when accessed"
                );
                
                // Property 2: Gateway response should be encrypted in the database
                // Query the raw database value
                $rawPayment = \DB::table('payments')
                    ->where('id', $payment->id)
                    ->first();
                
                // The raw value should NOT equal the plaintext
                $this->assertNotEquals(
                    $gatewayResponse,
                    $rawPayment->gateway_response,
                    "Gateway response should be encrypted in the database"
                );
                
                // Property 3: The encrypted value should be decryptable
                $decrypted = Crypt::decryptString($rawPayment->gateway_response);
                $this->assertEquals(
                    $gatewayResponse,
                    $decrypted,
                    "Encrypted gateway response should be decryptable to original value"
                );
                
                // Property 4: Each encryption should produce a unique ciphertext (due to IV)
                // Create another payment with the same gateway response
                $payment2 = Payment::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $petOwner->id,
                    'gateway' => $gateway,
                    'transaction_id' => 'TXN_' . uniqid(),
                    'amount' => $amount,
                    'service_charge' => $serviceCharge,
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'gateway_response' => $gatewayResponse,
                ]);
                
                $rawPayment2 = \DB::table('payments')
                    ->where('id', $payment2->id)
                    ->first();
                
                // Even with the same plaintext, encrypted values should differ (due to IV)
                $this->assertNotEquals(
                    $rawPayment->gateway_response,
                    $rawPayment2->gateway_response,
                    "Encryption should produce unique ciphertexts for the same plaintext (IV)"
                );
                
                // But both should decrypt to the same value
                $decrypted2 = Crypt::decryptString($rawPayment2->gateway_response);
                $this->assertEquals(
                    $gatewayResponse,
                    $decrypted2,
                    "Both encrypted values should decrypt to the same plaintext"
                );
            });
    }

    /**
     * Additional property test: Null gateway response handling
     * 
     * Tests that null gateway responses are handled correctly.
     */
    public function test_null_gateway_response_is_handled_correctly(): void
    {
        $petOwner = User::factory()->create(['role' => 'pet_owner']);
        $veterinarian = Veterinarian::factory()->create();
        
        $pet = Pet::create([
            'owner_id' => $petOwner->id,
            'name' => 'Test Pet',
            'species' => 'cat',
            'breed' => 'Persian',
            'age' => 2,
            'weight' => 4.5,
            'gender' => 'female',
        ]);
        
        $timeSlot = TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'is_available' => false,
            'is_blocked' => false,
        ]);
        
        $appointment = Appointment::create([
            'pet_owner_id' => $petOwner->id,
            'veterinarian_id' => $veterinarian->id,
            'pet_id' => $pet->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'pending',
        ]);
        
        // Create payment without gateway response
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $petOwner->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TXN_' . uniqid(),
            'amount' => 500.00,
            'service_charge' => 50.00,
            'total_amount' => 550.00,
            'status' => 'pending',
            'gateway_response' => null,
        ]);

        $retrievedPayment = Payment::find($payment->id);
        
        // Property: Null values should remain null
        $this->assertNull(
            $retrievedPayment->gateway_response,
            "Null gateway response should remain null"
        );
    }

    /**
     * Additional property test: Empty string gateway response
     * 
     * Tests that empty string gateway responses are encrypted.
     */
    public function test_empty_gateway_response_is_encrypted(): void
    {
        $petOwner = User::factory()->create(['role' => 'pet_owner']);
        $veterinarian = Veterinarian::factory()->create();
        
        $pet = Pet::create([
            'owner_id' => $petOwner->id,
            'name' => 'Test Pet',
            'species' => 'bird',
            'breed' => 'Parrot',
            'age' => 1,
            'weight' => 0.5,
            'gender' => 'male',
        ]);
        
        $timeSlot = TimeSlot::create([
            'veterinarian_id' => $veterinarian->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'is_available' => false,
            'is_blocked' => false,
        ]);
        
        $appointment = Appointment::create([
            'pet_owner_id' => $petOwner->id,
            'veterinarian_id' => $veterinarian->id,
            'pet_id' => $pet->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'pending',
        ]);
        
        // Create payment with empty gateway response
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $petOwner->id,
            'gateway' => 'nagad',
            'transaction_id' => 'TXN_' . uniqid(),
            'amount' => 300.00,
            'service_charge' => 50.00,
            'total_amount' => 350.00,
            'status' => 'completed',
            'gateway_response' => '',
        ]);

        $retrievedPayment = Payment::find($payment->id);
        
        // Property: Empty string should be retrievable
        $this->assertEquals(
            '',
            $retrievedPayment->gateway_response,
            "Empty gateway response should be retrievable"
        );
        
        // Check raw database value
        $rawPayment = \DB::table('payments')
            ->where('id', $payment->id)
            ->first();
        
        // Empty string should still be encrypted (not stored as empty)
        $this->assertNotEquals(
            '',
            $rawPayment->gateway_response,
            "Empty string should be encrypted in database"
        );
    }

    /**
     * Additional property test: Large gateway response encryption
     * 
     * Tests that large gateway responses are encrypted correctly.
     */
    public function test_large_gateway_response_is_encrypted(): void
    {
        $this->forAll(
            Generator\names()
        )
            ->withMaxSize(100)
            ->then(function ($gatewayResponseBase) {
                // Generate a large gateway response by repeating the base
                $gatewayResponse = str_repeat($gatewayResponseBase . '_', 10);
                
                $petOwner = User::factory()->create(['role' => 'pet_owner']);
                $veterinarian = Veterinarian::factory()->create();
                
                $pet = Pet::create([
                    'owner_id' => $petOwner->id,
                    'name' => 'Test Pet',
                    'species' => 'dog',
                    'breed' => 'Beagle',
                    'age' => 4,
                    'weight' => 12.0,
                    'gender' => 'female',
                ]);
                
                $timeSlot = TimeSlot::create([
                    'veterinarian_id' => $veterinarian->id,
                    'start_time' => now()->addDay(),
                    'end_time' => now()->addDay()->addHour(),
                    'is_available' => false,
                    'is_blocked' => false,
                ]);
                
                $appointment = Appointment::create([
                    'pet_owner_id' => $petOwner->id,
                    'veterinarian_id' => $veterinarian->id,
                    'pet_id' => $pet->id,
                    'time_slot_id' => $timeSlot->id,
                    'status' => 'pending',
                ]);
                
                // Create payment with large gateway response
                $payment = Payment::create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $petOwner->id,
                    'gateway' => 'bkash',
                    'transaction_id' => 'TXN_' . uniqid(),
                    'amount' => 1000.00,
                    'service_charge' => 50.00,
                    'total_amount' => 1050.00,
                    'status' => 'completed',
                    'gateway_response' => $gatewayResponse,
                ]);

                $retrievedPayment = Payment::find($payment->id);
                
                // Property: Large gateway response should be decrypted correctly
                $this->assertEquals(
                    $gatewayResponse,
                    $retrievedPayment->gateway_response,
                    "Large gateway response should be decrypted correctly"
                );
                
                // Verify it's encrypted in database
                $rawPayment = \DB::table('payments')
                    ->where('id', $payment->id)
                    ->first();
                
                $this->assertNotEquals(
                    $gatewayResponse,
                    $rawPayment->gateway_response,
                    "Large gateway response should be encrypted in database"
                );
            });
    }
}
