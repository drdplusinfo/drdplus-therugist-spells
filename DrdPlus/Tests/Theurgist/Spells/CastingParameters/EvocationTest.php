<?php
namespace DrdPlus\Tests\Theurgist\Spells\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tests\Theurgist\Spells\CastingParameters\Partials\PositiveCastingParameterTest;
use DrdPlus\Theurgist\Spells\CastingParameters\Evocation;

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