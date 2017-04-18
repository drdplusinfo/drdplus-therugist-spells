<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\Casting;
use Granam\Integer\PositiveInteger;
use PHPUnit\Framework\TestCase;

class CastingTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $timeTable = new TimeTable();
        $zeroCasting = new Casting(0, $timeTable);
        self::assertInstanceOf(PositiveInteger::class, $zeroCasting);
        self::assertSame(0, $zeroCasting->getValue());
        self::assertSame('0', (string)$zeroCasting);
        $zeroCastingTimeBonus = $zeroCasting->getCastingTimeBonus();
        self::assertEquals(new TimeBonus(0, $timeTable), $zeroCastingTimeBonus);

        $positiveCasting = new Casting(123, $timeTable);
        self::assertSame(123, $positiveCasting->getValue());
        self::assertSame('123', (string)$positiveCasting);
        $positiveCastingTimeBonus = $positiveCasting->getCastingTimeBonus();
        self::assertEquals(new TimeBonus(123, $timeTable), $positiveCastingTimeBonus);
    }
}