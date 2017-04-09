<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use Granam\Tests\Tools\TestWithMockery;

class AdditionByRealmsTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it_with_just_an_addition()
    {
        $additionByRealms = new AdditionByRealms('123');
        self::assertSame(123, $additionByRealms->getAddition());
        self::assertSame(1, $additionByRealms->getRealmIncrement());
        $sameAdditionByRealms = new AdditionByRealms('1=123');
        self::assertSame(123, $sameAdditionByRealms->getAddition());
        self::assertSame(1, $sameAdditionByRealms->getRealmIncrement());
    }

    /**
     * @test
     */
    public function I_can_create_it_with_realms_price()
    {
        $additionByRealms = new AdditionByRealms('456=789');
        self::assertSame(789, $additionByRealms->getAddition());
        self::assertSame(456, $additionByRealms->getRealmIncrement());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function I_can_not_create_it_without_value()
    {
        new AdditionByRealms('');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     * @expectedExceptionMessageRegExp ~1=2=3~
     */
    public function I_can_not_create_it_with_too_many_parts()
    {
        new AdditionByRealms('1=2=3');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function I_can_not_create_it_with_empty_realm_price()
    {
        new AdditionByRealms('=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @expectedExceptionMessageRegExp ~foo~
     */
    public function I_can_not_create_it_with_invalid_realm_price()
    {
        new AdditionByRealms('foo=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     * @expectedExceptionMessageRegExp ~5=~
     */
    public function I_can_not_create_it_with_empty_addition()
    {
        new AdditionByRealms('5=');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @expectedExceptionMessageRegExp ~bar~
     */
    public function I_can_not_create_it_with_invalid_addition()
    {
        new AdditionByRealms('13=bar');
    }
}