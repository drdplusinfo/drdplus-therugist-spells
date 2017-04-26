<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\TimeUnitCode;
use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameterTest;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Duration;

class DurationTest extends PositiveCastingParameterTest
{
    protected function I_can_create_it_with_zero()
    {
        $duration = new Duration(['0', '78=321']);
        self::assertSame(0, $duration->getValue());
        self::assertEquals(new AdditionByRealms('78=321'), $duration->getAdditionByRealms());
        self::assertSame('0 (' . $duration->getAdditionByRealms() . ')', (string)$duration);
    }

    protected function I_can_create_it_positive()
    {
        $duration = new Duration(['35689', '332211']);
        self::assertSame(35689, $duration->getValue());
        self::assertEquals(new AdditionByRealms('332211'), $duration->getAdditionByRealms());
        self::assertSame('35689 (' . $duration->getAdditionByRealms() . ')', (string)$duration);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\InvalidValueForPositiveCastingParameter
     * @expectedExceptionMessageRegExp ~-5~
     */
    public function I_can_not_create_it_negative()
    {
        new Duration(['-5'], new TimeTable());
    }

    /**
     * @test
     */
    public function I_can_get_duration_time()
    {
        $duration = new Duration(['23', '1=2']);
        $timeTable = new TimeTable();
        self::assertEquals(new Time(14, TimeUnitCode::ROUND, $timeTable), $duration->getDurationTime($timeTable));
    }
}