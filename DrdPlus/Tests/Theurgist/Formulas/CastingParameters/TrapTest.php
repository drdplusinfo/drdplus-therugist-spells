<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\Properties\PropertyCode;
use DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials\IntegerCastingParameterTest;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Trap;

class TrapTest extends IntegerCastingParameterTest
{

    protected function I_can_create_it_negative()
    {
        $trap = new Trap(['-456', '4=6', PropertyCode::INTELLIGENCE]);
        self::assertSame(-456, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), PropertyCode::getIt(PropertyCode::INTELLIGENCE));
        self::assertEquals(new AdditionByRealms('4=6'), $trap->getAdditionByRealms());
        self::assertSame('-456 intelligence (4=>6)', (string)$trap);
    }

    protected function I_can_create_it_with_zero()
    {
        $trap = new Trap(['0', '78=321', PropertyCode::CHARISMA]);
        self::assertSame(0, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), PropertyCode::getIt(PropertyCode::CHARISMA));
        self::assertEquals(new AdditionByRealms('78=321'), $trap->getAdditionByRealms());
        self::assertSame('0 charisma (78=>321)', (string)$trap);
    }

    protected function I_can_create_it_positive()
    {
        $trap = new Trap(['35689', '332211', PropertyCode::ENDURANCE]);
        self::assertSame(35689, $trap->getValue());
        self::assertSame($trap->getPropertyCode(), PropertyCode::getIt(PropertyCode::ENDURANCE));
        self::assertEquals(new AdditionByRealms('332211'), $trap->getAdditionByRealms());
        self::assertSame('35689 endurance (1=>332211)', (string)$trap);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfPropertyUsedForTrap
     * @expectedExceptionMessageRegExp ~goodness~
     */
    public function I_can_not_create_it_with_unknown_property()
    {
        new Trap(['35689', '332211', 'goodness']);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfPropertyUsedForTrap
     * @expectedExceptionMessageRegExp ~nothing~
     */
    public function I_can_not_create_it_without_property()
    {
        new Trap(['35689', '332211']);
    }

    /**
     * @test
     */
    public function I_can_get_its_clone_changed_by_addition()
    {
        $sutClass = self::getSutClass();
        /** @var Trap $original */
        $original = new $sutClass(['123', '456=789', PropertyCode::ENDURANCE]);
        self::assertSame($original, $original->setAddition(0));
        $increased = $original->setAddition(456);
        self::assertSame(579, $increased->getValue());
        self::assertSame(456, $increased->getAdditionByRealms()->getCurrentAddition());
        self::assertSame($original->getPropertyCode(), $increased->getPropertyCode());
        self::assertNotSame($original, $increased);

        $decreased = $original->setAddition(-579);
        self::assertSame(-456, $decreased->getValue());
        self::assertNotSame($original, $decreased);
        self::assertNotSame($original, $increased);
        self::assertSame($original->getPropertyCode(), $decreased->getPropertyCode());
        self::assertSame(-579, $decreased->getAdditionByRealms()->getCurrentAddition());
    }
}