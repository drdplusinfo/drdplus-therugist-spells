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
        self::assertSame(123, $additionByRealms->getDefaultAddition());
        self::assertSame(1, $additionByRealms->getRealmIncrementPerAddition());
        self::assertSame($additionByRealms->getDefaultAddition(), $additionByRealms->getCurrentAddition());
        self::assertSame($additionByRealms->getRealmIncrementPerAddition(), $additionByRealms->getCurrentRealmIncrement());

        $sameAdditionByRealms = new AdditionByRealms('1=123');
        self::assertSame(123, $sameAdditionByRealms->getDefaultAddition());
        self::assertSame(1, $sameAdditionByRealms->getRealmIncrementPerAddition());
        self::assertSame($sameAdditionByRealms->getDefaultAddition(), $sameAdditionByRealms->getCurrentAddition());
        self::assertSame($sameAdditionByRealms->getRealmIncrementPerAddition(), $sameAdditionByRealms->getCurrentRealmIncrement());
    }

    /**
     * @test
     */
    public function I_can_create_it_with_realms_price()
    {
        $additionByRealms = new AdditionByRealms('456=789');
        self::assertSame(789, $additionByRealms->getDefaultAddition());
        self::assertSame(456, $additionByRealms->getRealmIncrementPerAddition());
        self::assertSame($additionByRealms->getDefaultAddition(), $additionByRealms->getCurrentAddition());
        self::assertSame($additionByRealms->getRealmIncrementPerAddition(), $additionByRealms->getCurrentRealmIncrement());
    }

    /**
     * @test
     */
    public function I_can_create_it_with_custom_current_addition()
    {
        $additionByRealms = new AdditionByRealms('2=3', 7);
        self::assertSame(3, $additionByRealms->getDefaultAddition());
        self::assertSame(2, $additionByRealms->getRealmIncrementPerAddition());
        self::assertSame(7, $additionByRealms->getCurrentAddition());
        self::assertSame(5 /* 7 / 3 * 2, round up */, $additionByRealms->getCurrentRealmIncrement());
    }

    /**
     * @test
     */
    public function I_can_increase_current_addition()
    {
        $additionByRealms = new AdditionByRealms(5);
        self::assertSame(5, $additionByRealms->getDefaultAddition());
        self::assertSame(5, $additionByRealms->getCurrentAddition());
        $same = $additionByRealms->add(0);
        self::assertSame($same, $additionByRealms);
        $increased = $additionByRealms->add(3);
        self::assertSame(5, $additionByRealms->getCurrentAddition());
        self::assertNotSame($additionByRealms, $increased);
        self::assertSame(8, $increased->getValue());
    }

    /**
     * @test
     */
    public function I_can_decrease_current_addition()
    {
        $additionByRealms = new AdditionByRealms(5);
        self::assertSame(5, $additionByRealms->getDefaultAddition());
        self::assertSame(5, $additionByRealms->getCurrentAddition());
        $same = $additionByRealms->sub(0);
        self::assertSame($same, $additionByRealms);
        $increased = $additionByRealms->sub(7);
        self::assertSame(5, $additionByRealms->getCurrentAddition());
        self::assertNotSame($additionByRealms, $increased);
        self::assertSame(-2, $increased->getValue());
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