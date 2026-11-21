<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DiscountRoundingTest extends CIUnitTestCase
{
    public static function amounts(): array
    {
        return [
            'half-cent student quote' => [155.50, 15.00, 132.18],
            'half-cent minimum fare' => [0.01, 50.00, 0.01],
            'half-cent rounds upward' => [0.03, 50.00, 0.02],
            'fractional percentage' => [100.03, 12.50, 87.53],
            'two-decimal percentage' => [123.45, 7.25, 114.50],
            'zero fare' => [0.00, 15.00, 0.00],
            'zero discount' => [155.50, 0.00, 155.50],
            'full discount' => [155.50, 100.00, 0.00],
            'maximum stored fare' => [99999999.99, 0.00, 99999999.99],
            'maximum fare with fractional discount' => [99999999.99, 99.99, 10000.00],
        ];
    }

    #[DataProvider('amounts')]
    public function testConfiguredMoneyAndRatesRoundConsistently(float $fare, float $percent, float $expected): void
    {
        helper('fare');
        $this->assertSame($expected, discounted_fare_amount($fare, $percent));
    }
}
