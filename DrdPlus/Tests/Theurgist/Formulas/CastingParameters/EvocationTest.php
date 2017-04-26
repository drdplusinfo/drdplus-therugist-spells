<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\TimeUnitCode;
use DrdPlus\Tables\Measurements\Time\Time;
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
        $zeroEvocation = new Evocation(0);
        self::assertInstanceOf(PositiveInteger::class, $zeroEvocation);
        self::assertSame(0, $zeroEvocation->getValue());
        self::assertSame('0', (string)$zeroEvocation);
        $timeTable = new TimeTable();
        $zeroEvocationTime = $zeroEvocation->getEvocationTime($timeTable);
        self::assertEquals(new Time(1, TimeUnitCode::ROUND, $timeTable), $zeroEvocationTime);

        $positiveEvocation = new Evocation(123, $timeTable);
        self::assertSame(123, $positiveEvocation->getValue());
        self::assertSame('123', (string)$positiveEvocation);
        $positiveEvocationTime = $positiveEvocation->getEvocationTime($timeTable);
        self::assertEquals((new TimeBonus(123, $timeTable))->getTime(), $positiveEvocationTime);
    }
}