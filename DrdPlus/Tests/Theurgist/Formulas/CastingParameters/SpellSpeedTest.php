<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellSpeed;
use Granam\Tests\Tools\TestWithMockery;

class SpellSpeedTest extends TestWithMockery
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
        $speed = new SpellSpeed(['-12', '4=6'], $speedTable = new SpeedTable());
        self::assertSame(-12, $speed->getValue());
        self::assertEquals((new SpeedBonus(-12, $speedTable))->getSpeed(), $speed->getSpeed());
        self::assertEquals(new AdditionByRealms('4=6'), $speed->getAdditionByRealms());
        self::assertSame('-12 (' . $speed->getAdditionByRealms() . ')', (string)$speed);
    }

    protected function I_can_create_it_with_zero()
    {
        $speed = new SpellSpeed(['0', '78=321'], $distanceTable = new SpeedTable());
        self::assertSame(0, $speed->getValue());
        self::assertEquals((new SpeedBonus(0, $distanceTable))->getSpeed(), $speed->getSpeed());
        self::assertEquals(new AdditionByRealms('78=321'), $speed->getAdditionByRealms());
        self::assertSame('0 (' . $speed->getAdditionByRealms() . ')', (string)$speed);
    }

    protected function I_can_create_it_positive()
    {
        $speed = new SpellSpeed(['35', '332211'], $distanceTable = new SpeedTable());
        self::assertSame(35, $speed->getValue());
        self::assertEquals((new SpeedBonus(35, $distanceTable))->getSpeed(), $speed->getSpeed());
        self::assertEquals(new AdditionByRealms('332211'), $speed->getAdditionByRealms());
        self::assertSame('35 (' . $speed->getAdditionByRealms() . ')', (string)$speed);
    }
}