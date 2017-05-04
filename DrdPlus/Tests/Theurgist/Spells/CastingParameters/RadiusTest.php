<?php
namespace DrdPlus\Tests\Theurgist\Spells\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tests\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameterTest;
use DrdPlus\Theurgist\Spells\CastingParameters\Radius;

class RadiusTest extends IntegerCastingParameterTest
{
    /**
     * @test
     */
    public function I_can_get_distance()
    {
        $radius = new Radius(['63', '332211']);
        self::assertSame(63, $radius->getValue());
        self::assertEquals(
            (new DistanceBonus(63, $distanceTable = new DistanceTable()))->getDistance(),
            $radius->getDistance($distanceTable)
        );
    }
}