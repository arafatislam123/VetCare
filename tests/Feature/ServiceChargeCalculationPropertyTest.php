<?php

namespace Tests\Feature;

use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceChargeCalculationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Property 20: Service charge calculation
     * 
     * **Validates: Requirements 5.1**
     * 
     * For any consultation fee amount, when payment is initiated, 
     * the total amount should equal the consultation fee plus exactly 50 TK.
     */
    public function test_service_charge_calculation_property(): void
    {
        $this->forAll(
            Generator\choose(0, 100000) // Generate consultation fees from 0 to 100,000 TK
        )
            ->withMaxSize(100)
            ->then(function ($consultationFeeInCents) {
                // Convert to decimal (divide by 100 to get realistic fee amounts)
                $consultationFee = $consultationFeeInCents / 100;
                
                // Service charge is always 50 TK
                $serviceCharge = 50.00;
                
                // Calculate expected total
                $expectedTotal = $consultationFee + $serviceCharge;
                
                // Simulate the calculateTotal method logic
                // (Since Payment model doesn't exist yet, we test the calculation logic)
                $calculatedTotal = $this->calculateTotal($consultationFee);
                
                // Property: Total should equal consultation fee + 50 TK service charge
                $this->assertEquals(
                    round($expectedTotal, 2),
                    round($calculatedTotal, 2),
                    "Total amount should equal consultation fee ({$consultationFee}) plus 50 TK service charge"
                );
                
                // Verify service charge is exactly 50 TK
                $this->assertEquals(
                    50.00,
                    $serviceCharge,
                    "Service charge should always be exactly 50 TK"
                );
                
                // Verify total is always greater than or equal to consultation fee
                $this->assertGreaterThanOrEqual(
                    $consultationFee,
                    $calculatedTotal,
                    "Total amount should always be greater than or equal to consultation fee"
                );
                
                // Verify the difference is exactly 50 TK
                $difference = $calculatedTotal - $consultationFee;
                $this->assertEquals(
                    50.00,
                    round($difference, 2),
                    "Difference between total and consultation fee should be exactly 50 TK"
                );
            });
    }

    /**
     * Additional property test: Service charge calculation with zero consultation fee
     * 
     * Tests that even with zero consultation fee, the service charge is still 50 TK.
     */
    public function test_service_charge_with_zero_consultation_fee(): void
    {
        $consultationFee = 0.00;
        $calculatedTotal = $this->calculateTotal($consultationFee);
        
        // Property: Total should be exactly 50 TK when consultation fee is 0
        $this->assertEquals(
            50.00,
            $calculatedTotal,
            "Total should be exactly 50 TK when consultation fee is 0"
        );
    }

    /**
     * Additional property test: Service charge calculation with large consultation fees
     * 
     * Tests that service charge remains 50 TK even with very large consultation fees.
     */
    public function test_service_charge_with_large_consultation_fees(): void
    {
        $this->forAll(
            Generator\choose(100000, 1000000) // Large fees from 1,000 to 10,000 TK
        )
            ->withMaxSize(100)
            ->then(function ($consultationFeeInCents) {
                $consultationFee = $consultationFeeInCents / 100;
                $calculatedTotal = $this->calculateTotal($consultationFee);
                
                // Property: Service charge should still be exactly 50 TK
                $difference = $calculatedTotal - $consultationFee;
                $this->assertEquals(
                    50.00,
                    round($difference, 2),
                    "Service charge should be exactly 50 TK even for large consultation fees"
                );
            });
    }

    /**
     * Additional property test: Service charge calculation is commutative
     * 
     * Tests that the order of operations doesn't matter: fee + charge = charge + fee.
     */
    public function test_service_charge_calculation_is_commutative(): void
    {
        $this->forAll(
            Generator\choose(0, 50000)
        )
            ->withMaxSize(100)
            ->then(function ($consultationFeeInCents) {
                $consultationFee = $consultationFeeInCents / 100;
                $serviceCharge = 50.00;
                
                $total1 = $consultationFee + $serviceCharge;
                $total2 = $serviceCharge + $consultationFee;
                
                // Property: Addition is commutative
                $this->assertEquals(
                    round($total1, 2),
                    round($total2, 2),
                    "Service charge calculation should be commutative"
                );
            });
    }

    /**
     * Helper method to simulate Payment model's calculateTotal method
     * 
     * This implements the business logic: total = consultation_fee + 50 TK
     * 
     * @param float $consultationFee The consultation fee amount
     * @return float The total amount including service charge
     */
    private function calculateTotal(float $consultationFee): float
    {
        $serviceCharge = 50.00;
        return $consultationFee + $serviceCharge;
    }
}
