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
        $radius = new Radius(['-13', '4=6'], $distanceTable = new DistanceTable());
        self::assertSame(-13, $radius->getValue());
        self::assertEquals((new DistanceBonus(-13, $distanceTable))->getDistance(), $radius->getDistance());
        self::assertEquals(new AdditionByRealms('4=6'), $radius->getAdditionByRealms());
        self::assertSame('-13 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }

    protected function I_can_create_it_with_zero()
    {
        $radius = new Radius(['0', '78=321'], $distanceTable = new DistanceTable());
        self::assertSame(0, $radius->getValue());
        self::assertEquals((new DistanceBonus(0, $distanceTable))->getDistance(), $radius->getDistance());
        self::assertEquals(new AdditionByRealms('78=321'), $radius->getAdditionByRealms());
        self::assertSame('0 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }

    protected function I_can_create_it_positive()
    {
        $radius = new Radius(['63', '332211'], $distanceTable = new DistanceTable());
        self::assertSame(63, $radius->getValue());
        self::assertEquals((new DistanceBonus(63, $distanceTable))->getDistance(), $radius->getDistance());
        self::assertEquals(new AdditionByRealms('332211'), $radius->getAdditionByRealms());
        self::assertSame('63 (' . $radius->getAdditionByRealms() . ')', (string)$radius);
    }
}