<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\Properties\PropertyCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Trap;
use Granam\Tests\Tools\TestWithMockery;

class TrapTest extends TestWithMockery
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
        $trap = new Trap(['-456', '4=6'], $propertyCode = PropertyCode::getIt(PropertyCode::INTELLIGENCE));
        self::assertSame(-456, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), $propertyCode);
        self::assertEquals(new AdditionByRealms('4=6'), $trap->getAdditionByRealms());
        self::assertSame('-456 intelligence (4=>6)', (string)$trap);
    }

    protected function I_can_create_it_with_zero()
    {
        $trap = new Trap(['0', '78=321'], $propertyCode = PropertyCode::getIt(PropertyCode::CHARISMA));
        self::assertSame(0, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), $propertyCode);
        self::assertEquals(new AdditionByRealms('78=321'), $trap->getAdditionByRealms());
        self::assertSame('0 charisma (78=>321)', (string)$trap);
    }

    protected function I_can_create_it_positive()
    {
        $trap = new Trap(['35689', '332211'], $propertyCode = PropertyCode::getIt(PropertyCode::ENDURANCE));
        self::assertSame(35689, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), $propertyCode);
        self::assertEquals(new AdditionByRealms('332211'), $trap->getAdditionByRealms());
        self::assertSame('35689 endurance (1=>332211)', (string)$trap);
    }
}