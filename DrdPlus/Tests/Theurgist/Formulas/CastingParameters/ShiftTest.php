<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Shift;
use Granam\Tests\Tools\TestWithMockery;

class ShiftTest extends TestWithMockery
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
        $shift = new Shift(['-456', '4=6'], $distanceTable = new DistanceTable());
        self::assertSame(-456, $shift->getValue());
        self::assertEquals(new DistanceBonus(-456, $distanceTable), $shift->getDistance());
        self::assertEquals(new AdditionByRealms('4=6'), $shift->getAdditionByRealms());
        self::assertSame('-456 (' . $shift->getAdditionByRealms() . ')', (string)$shift);
    }

    protected function I_can_create_it_with_zero()
    {
        $shift = new Shift(['0', '78=321'], $distanceTable = new DistanceTable());
        self::assertSame(0, $shift->getValue());
        self::assertEquals(new DistanceBonus(0, $distanceTable), $shift->getDistance());
        self::assertEquals(new AdditionByRealms('78=321'), $shift->getAdditionByRealms());
        self::assertSame('0 (' . $shift->getAdditionByRealms() . ')', (string)$shift);
    }

    protected function I_can_create_it_positive()
    {
        $shift = new Shift(['35689', '332211'], $distanceTable = new DistanceTable());
        self::assertSame(35689, $shift->getValue());
        self::assertEquals(new DistanceBonus(35689, $distanceTable), $shift->getDistance());
        self::assertEquals(new AdditionByRealms('332211'), $shift->getAdditionByRealms());
        self::assertSame('35689 (' . $shift->getAdditionByRealms() . ')', (string)$shift);
    }
}