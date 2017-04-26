<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials\IntegerCastingParameterTest;
use DrdPlus\Theurgist\Formulas\CastingParameters\EpicenterShift;

class EpicenterShiftTest extends IntegerCastingParameterTest
{
    /**
     * @test
     */
    public function I_can_create_it_positive()
    {
        $shift = new EpicenterShift(['35', '332211']);
        self::assertSame(35, $shift->getValue());
        self::assertEquals(
            (new DistanceBonus(35, $distanceTable = new DistanceTable()))->getDistance(),
            $shift->getDistance($distanceTable)
        );
    }
}