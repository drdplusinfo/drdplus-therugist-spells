<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\IntegerCastingParameter;
use Granam\Tests\Tools\TestWithMockery;

abstract class IntegerCastingParameterTest extends TestWithMockery
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
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $sut */
        $sut = new $sutClass(['-456', '4=6']);
        self::assertSame(-456, $sut->getValue());
        self::assertEquals(new AdditionByRealms('4=6'), $sut->getAdditionByRealms());
        self::assertSame('-456 (' . $sut->getAdditionByRealms() . ')', (string)$sut);
    }

    protected function I_can_create_it_with_zero()
    {
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $sut */
        $sut = new $sutClass(['0', '78=321']);
        self::assertSame(0, $sut->getValue());
        self::assertEquals(new AdditionByRealms('78=321'), $sut->getAdditionByRealms());
        self::assertSame('0 (' . $sut->getAdditionByRealms() . ')', (string)$sut);
    }

    protected function I_can_create_it_positive()
    {
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $sut */
        $sut = new $sutClass(['35689', '332211']);
        self::assertSame(35689, $sut->getValue());
        self::assertEquals(new AdditionByRealms('332211'), $sut->getAdditionByRealms());
        self::assertSame('35689 (' . $sut->getAdditionByRealms() . ')', (string)$sut);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForIntegerCastingParameter
     * @expectedExceptionMessageRegExp ~infinite~
     */
    public function I_can_not_create_it_non_numeric()
    {
        $sutClass = self::getSutClass();
        new $sutClass(['infinite', '332211']);
    }
}