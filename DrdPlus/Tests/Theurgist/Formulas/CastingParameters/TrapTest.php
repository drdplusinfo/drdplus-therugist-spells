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
    public function I_can_get_its_clone_with_increased_value()
    {
        $sutClass = self::getSutClass();
        /** @var Trap $original */
        $original = new $sutClass(['123', '456=789', PropertyCode::ENDURANCE]);
        self::assertSame($original, $original->add(0));
        $increased = $original->add(456);
        self::assertSame($original->getValue() + 456, $increased->getValue());
        self::assertEquals($original->getAdditionByRealms(), $increased->getAdditionByRealms());
        self::assertSame($original->getPropertyCode(), $increased->getPropertyCode());
        self::assertNotSame($original, $increased);

        $zeroed = $increased->add(-579);
        self::assertSame(0, $zeroed->getValue());
        self::assertNotSame($original, $zeroed);
        self::assertNotSame($original, $increased);
        self::assertSame($original->getPropertyCode(), $zeroed->getPropertyCode());
        self::assertEquals($original->getAdditionByRealms(), $zeroed->getAdditionByRealms());
    }

    /**
     * @test
     */
    public function I_can_get_its_clone_with_decreased_value()
    {
        $sutClass = self::getSutClass();
        /** @var Trap $original */
        $original = new $sutClass(['123', '456=789', PropertyCode::INTELLIGENCE]);
        self::assertSame($original, $original->sub(0));
        $decreased = $original->sub(111);
        self::assertSame($original->getValue() - 111, $decreased->getValue());
        self::assertEquals($original->getAdditionByRealms(), $decreased->getAdditionByRealms());
        self::assertSame($original->getPropertyCode(), $decreased->getPropertyCode());
        self::assertNotSame($original, $decreased);

        $restored = $decreased->sub(-111);
        self::assertSame($original->getValue(), $restored->getValue());
        self::assertNotSame($original, $restored);
        self::assertNotSame($original, $decreased);
        self::assertSame($original->getPropertyCode(), $restored->getPropertyCode());
        self::assertEquals($original->getAdditionByRealms(), $restored->getAdditionByRealms());
    }
}