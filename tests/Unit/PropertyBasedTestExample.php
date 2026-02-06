<?php

namespace Tests\Unit;

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class PropertyBasedTestExample extends TestCase
{
    use TestTrait;

    /**
     * Example property-based test to verify Eris is working.
     * Property: For any two integers, their sum should be commutative.
     */
    public function test_addition_is_commutative(): void
    {
        $this->forAll(
            Generator\int(),
            Generator\int()
        )->then(function ($a, $b) {
            $this->assertEquals($a + $b, $b + $a);
        });
    }

    /**
     * Example property-based test for string concatenation.
     * Property: For any string, concatenating it with an empty string returns the original string.
     */
    public function test_string_concatenation_with_empty_string(): void
    {
        $this->forAll(
            Generator\string()
        )->then(function ($str) {
            $this->assertEquals($str, $str . '');
            $this->assertEquals($str, '' . $str);
        });
    }
}
