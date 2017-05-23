<?php
namespace DrdPlus\Tests\Theurgist\Spells\SpellParameters;

use DrdPlus\Codes\DistanceUnitCode;
use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tests\Theurgist\Spells\SpellParameters\Partials\CastingParameterTest;
use DrdPlus\Theurgist\Spells\SpellParameters\EpicenterShift;

class EpicenterShiftTest extends CastingParameterTest
{
    /**
     * @test
     */
    public function I_can_get_its_distance()
    {
        $shift = new EpicenterShift(['35', '332211']);
        self::assertSame(35, $shift->getValue());
        self::assertEquals(
            (new DistanceBonus(35, $distanceTable = new DistanceTable()))->getDistance(),
            $shift->getDistance($distanceTable)
        );
    }

    /**
     * @test
     */
    public function I_can_create_it_with_precise_distance()
    {
        $distanceTable = new DistanceTable();
        $shift = new EpicenterShift(['40', '332211'], $distance = new Distance(102, DistanceUnitCode::METER, $distanceTable));
        self::assertSame(40, $shift->getValue());
        self::assertEquals(new DistanceBonus(40, $distanceTable), $shift->getDistance($distanceTable)->getBonus());
        self::assertGreaterThan(
            (new DistanceBonus(40, $distanceTable))->getDistance()->getValue(),
            $shift->getDistance($distanceTable)->getValue()
        );
        self::assertSame($distance, $shift->getDistance($distanceTable));
    }
}