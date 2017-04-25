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
        $zeroCasting = new Evocation(0);
        self::assertInstanceOf(PositiveInteger::class, $zeroCasting);
        self::assertSame(0, $zeroCasting->getValue());
        self::assertSame('0', (string)$zeroCasting);
        $timeTable = new TimeTable();
        $zeroCastingTime = $zeroCasting->getCastingTime($timeTable);
        self::assertEquals((new TimeBonus(0, $timeTable))->getTime(), $zeroCastingTime);

        $positiveCasting = new Evocation(123, $timeTable);
        self::assertSame(123, $positiveCasting->getValue());
        self::assertSame('123', (string)$positiveCasting);
        $positiveCastingTimeBonus = $positiveCasting->getCastingTime($timeTable);
        self::assertEquals((new TimeBonus(123, $timeTable))->getTime(), $positiveCastingTimeBonus);
    }
}