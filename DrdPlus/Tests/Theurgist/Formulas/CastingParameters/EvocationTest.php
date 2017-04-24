<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\Evocation;
use Granam\Integer\PositiveInteger;
use PHPUnit\Framework\TestCase;

class EvocationTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $timeTable = new TimeTable();
        $zeroCasting = new Evocation(0, $timeTable);
        self::assertInstanceOf(PositiveInteger::class, $zeroCasting);
        self::assertSame(0, $zeroCasting->getValue());
        self::assertSame('0', (string)$zeroCasting);
        $zeroCastingTime = $zeroCasting->getCastingTime();
        self::assertEquals((new TimeBonus(0, $timeTable))->getTime(), $zeroCastingTime);

        $positiveCasting = new Evocation(123, $timeTable);
        self::assertSame(123, $positiveCasting->getValue());
        self::assertSame('123', (string)$positiveCasting);
        $positiveCastingTimeBonus = $positiveCasting->getCastingTime();
        self::assertEquals((new TimeBonus(123, $timeTable))->getTime(), $positiveCastingTimeBonus);
    }
}