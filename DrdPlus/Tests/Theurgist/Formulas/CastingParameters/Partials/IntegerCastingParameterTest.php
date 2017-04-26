<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\IntegerCastingParameter;
use Granam\Tests\Tools\TestWithMockery;

abstract class IntegerCastingParameterTest extends TestWithMockery
{
    use IntegerAddAndSubTestTrait;

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
     * @expectedExceptionMessageRegExp ~123~
     */
    public function I_can_not_create_it_with_invalid_points_to_annotation()
    {
        $reflectionMethod = new \ReflectionMethod(IntegerCastingParameter::class, '__construct');
        $reflectionMethod->invoke($this->mockery(IntegerCastingParameter::class), [123]);
    }

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
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\InvalidValueForIntegerCastingParameter
     * @expectedExceptionMessageRegExp ~infinite~
     */
    public function I_can_not_create_it_non_numeric()
    {
        $sutClass = self::getSutClass();
        new $sutClass(['infinite', '332211']);
    }

    /**
     * @test
     */
    public function I_can_get_its_clone_with_increased_value()
    {
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $original */
        $original = new $sutClass(['123', '456=789']);
        self::assertSame($original, $original->add(0));
        $increased = $original->add(456);
        self::assertSame($original->getValue() + 456, $increased->getValue());
        self::assertEquals($original->getAdditionByRealms(), $increased->getAdditionByRealms());
        self::assertNotSame($original, $increased);

        $zeroed = $increased->add(-579);
        self::assertSame(0, $zeroed->getValue());
        self::assertNotSame($original, $zeroed);
        self::assertNotSame($original, $increased);
        self::assertEquals($original->getAdditionByRealms(), $zeroed->getAdditionByRealms());
    }

    /**
     * @test
     */
    public function I_can_get_its_clone_with_decreased_value()
    {
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $original */
        $original = new $sutClass(['123', '456=789']);
        self::assertSame($original, $original->sub(0));
        $decreased = $original->sub(111);
        self::assertSame($original->getValue() - 111, $decreased->getValue());
        self::assertEquals($original->getAdditionByRealms(), $decreased->getAdditionByRealms());
        self::assertNotSame($original, $decreased);

        $restored = $decreased->sub(-111);
        self::assertSame($original->getValue(), $restored->getValue());
        self::assertNotSame($original, $restored);
        self::assertNotSame($original, $decreased);
        self::assertEquals($original->getAdditionByRealms(), $restored->getAdditionByRealms());
    }

}