<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Radius;
use Granam\Tests\Tools\TestWithMockery;

class RadiusTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it()
    {
        $this->I_can_create_it_negative();
        $this->I_can_create_it_with_zero();
        $this->I_can_create_it_positive();
    }

    protected function I_can_create_it_negative()
    {
        $radius = new Radius(['-456', '4=6'], $distanceTable = new DistanceTable());
        self::assertSame(-456, $radius->getValue());
        self::assertEquals(new DistanceBonus(-456, $distanceTable), $radius->getDistanceBonus());
        self::assertEquals(new AdditionByRealms('4=6'), $radius->getAdditionByRealms());
        self::assertSame('-456 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }

    protected function I_can_create_it_with_zero()
    {
        $radius = new Radius(['0', '78=321'], $distanceTable = new DistanceTable());
        self::assertSame(0, $radius->getValue());
        self::assertEquals(new DistanceBonus(0, $distanceTable), $radius->getDistanceBonus());
        self::assertEquals(new AdditionByRealms('78=321'), $radius->getAdditionByRealms());
        self::assertSame('0 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }

    protected function I_can_create_it_positive()
    {
        $radius = new Radius(['35689', '332211'], $distanceTable = new DistanceTable());
        self::assertSame(35689, $radius->getValue());
        self::assertEquals(new DistanceBonus(35689, $distanceTable), $radius->getDistanceBonus());
        self::assertEquals(new AdditionByRealms('332211'), $radius->getAdditionByRealms());
        self::assertSame('35689 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }
}