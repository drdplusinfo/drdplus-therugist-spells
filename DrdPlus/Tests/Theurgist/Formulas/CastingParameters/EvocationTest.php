<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameterTest;
use DrdPlus\Theurgist\Formulas\CastingParameters\Evocation;

class EvocationTest extends PositiveCastingParameterTest
{
    /**
     * @test
     */
    public function I_can_get_evocation_time()
    {
        $evocation = new Evocation([123]);
        self::assertEquals(
            (new TimeBonus(123, $timeTable = new TimeTable()))->getTime(),
            $evocation->getEvocationTime($timeTable)
        );
    }
}