<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\Casting;
use PHPUnit\Framework\TestCase;

class CastingTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $zeroCasting = new Casting(0);
        self::assertSame(0, $zeroCasting->getValue());
        self::assertSame('0', (string)$zeroCasting);

        $positiveCasting = new Casting(123);
        self::assertSame(123, $positiveCasting->getValue());
        self::assertSame('123', (string)$positiveCasting);
    }
}