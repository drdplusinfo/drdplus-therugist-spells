<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Duration;

class DurationTest extends PositiveCastingParameterTest
{
    protected function I_can_create_it_with_zero()
    {
        $duration = new Duration(['0', '78=321'], $timeTable = new TimeTable());
        self::assertSame(0, $duration->getValue());
        self::assertEquals(new TimeBonus(0, $timeTable), $duration->getDurationTimeBonus());
        self::assertEquals(new AdditionByRealms('78=321'), $duration->getAdditionByRealms());
        self::assertSame('0 (' . $duration->getAdditionByRealms() . ')', (string)$duration);
    }

    protected function I_can_create_it_positive()
    {
        $duration = new Duration(['35689', '332211'], $timeTable = new TimeTable());
        self::assertSame(35689, $duration->getValue());
        self::assertEquals(new TimeBonus(35689, $timeTable), $duration->getDurationTimeBonus());
        self::assertEquals(new AdditionByRealms('332211'), $duration->getAdditionByRealms());
        self::assertSame('35689 (' . $duration->getAdditionByRealms() . ')', (string)$duration);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForPositiveCastingParameter
     * @expectedExceptionMessageRegExp ~-5~
     */
    public function I_can_not_create_it_negative()
    {
        new Duration(['-5'], new TimeTable());
    }
}