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

    /**
     * @test
     */
    public function I_can_get_its_clone_with_increased_value()
    {
        $sutClass = self::getSutClass();
        /** @var IntegerCastingParameter $original */
        $original = new $sutClass(['123', '456=789']);
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

    /**
     * @test
     */
    public function I_get_whispered_current_class_as_return_value_of_add_and_sub()
    {
        $reflectionClass = new \ReflectionClass(self::getSutClass());
        $classBaseName = preg_replace('~^.*[\\\](\w+)$~', '$1', self::getSutClass());
        $add = $reflectionClass->getMethod('add');
        $sub = $reflectionClass->getMethod('sub');
        if (strpos($add->getDocComment(), $classBaseName) !== false && strpos($sub->getDocComment(), $classBaseName) !== false) {
            self::assertSame($phpDoc = <<<PHPDOC
/**
 * @param int|float|NumberInterface \$value
 * @return {$classBaseName}
 * @throws \Granam\Integer\Tools\Exceptions\Exception
 */
PHPDOC
                , preg_replace('~ {2,}~', ' ', $add->getDocComment()),
                "Expected:\n$phpDoc\nfor method 'add'"
            );
            self::assertSame($phpDoc = <<<PHPDOC
/**
 * @param int|float|NumberInterface \$value
 * @return {$classBaseName}
 * @throws \Granam\Integer\Tools\Exceptions\Exception
 */
PHPDOC
                , preg_replace('~ {2,}~', ' ', $sub->getDocComment()),
                "Expected:\n$phpDoc\nfor method 'sub'"
            );
        } else {
            self::assertSame($phpDoc = <<<PHPDOC
/**
 * @method {$classBaseName} add(\$value)
 * @method {$classBaseName} sub(\$value)
 */
PHPDOC
                , $reflectionClass->getDocComment(),
                "Expected:\n$phpDoc"
            );
        }
    }
}