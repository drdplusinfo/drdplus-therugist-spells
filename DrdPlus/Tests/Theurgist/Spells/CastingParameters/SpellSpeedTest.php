<?php
namespace DrdPlus\Tests\Theurgist\Spells\CastingParameters;

use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedTable;
use DrdPlus\Tests\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameterTest;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;

class SpellSpeedTest extends IntegerCastingParameterTest
{
    /**
     * @test
     */
    public function I_can_get_speed()
    {
        $speed = new SpellSpeed(['35', '332211']);
        self::assertSame(35, $speed->getValue());
        self::assertEquals(
            (new SpeedBonus(35, $distanceTable = new SpeedTable()))->getSpeed(),
            $speed->getSpeed($distanceTable)
        );
    }
}