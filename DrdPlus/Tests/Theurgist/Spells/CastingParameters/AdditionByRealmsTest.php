<?php
namespace DrdPlus\Tests\Theurgist\Spells\CastingParameters;

use DrdPlus\Theurgist\Spells\CastingParameters\AdditionByRealms;
use Granam\Tests\Tools\TestWithMockery;

class AdditionByRealmsTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it_with_just_an_addition()
    {
        $additionByRealms = new AdditionByRealms('123');
        self::assertSame(123, $additionByRealms->getAdditionStep());
        self::assertSame(1, $additionByRealms->getRealmsOfAdditionStep());
        self::assertSame(0, $additionByRealms->getCurrentAddition());
        self::assertSame(0, $additionByRealms->getCurrentRealmsIncrement());
        self::assertSame('0 {1=>123}', (string)$additionByRealms);

        $sameAdditionByRealms = new AdditionByRealms('1=123');
        self::assertSame(123, $sameAdditionByRealms->getAdditionStep());
        self::assertSame(1, $sameAdditionByRealms->getRealmsOfAdditionStep());
        self::assertSame(0, $sameAdditionByRealms->getCurrentAddition());
        self::assertSame(0, $sameAdditionByRealms->getCurrentRealmsIncrement());
        self::assertSame('0 {1=>123}', (string)$additionByRealms);
    }

    /**
     * @test
     */
    public function I_can_create_it_with_realms_price()
    {
        $additionByRealms = new AdditionByRealms('456=789');
        self::assertSame(789, $additionByRealms->getAdditionStep());
        self::assertSame(456, $additionByRealms->getRealmsOfAdditionStep());
        self::assertSame(0, $additionByRealms->getCurrentAddition());
        self::assertSame(0, $additionByRealms->getCurrentRealmsIncrement());
        self::assertSame('0 {456=>789}', (string)$additionByRealms);
    }

    /**
     * @test
     */
    public function I_can_create_it_with_custom_current_addition()
    {
        $additionByRealms = new AdditionByRealms('2=3', 7);
        self::assertSame(3, $additionByRealms->getAdditionStep());
        self::assertSame(2, $additionByRealms->getRealmsOfAdditionStep());
        self::assertSame(7, $additionByRealms->getCurrentAddition());
        self::assertSame(5 /* 7 / 3 * 2, round up */, $additionByRealms->getCurrentRealmsIncrement());
        self::assertSame('7 {2=>3}', (string)$additionByRealms);
    }

    /**
     * @test
     */
    public function I_can_increase_current_addition()
    {
        $additionByRealms = new AdditionByRealms(5);
        self::assertSame(5, $additionByRealms->getAdditionStep());
        self::assertSame(0, $additionByRealms->getCurrentAddition());
        self::assertSame('0 {1=>5}', (string)$additionByRealms);
        $same = $additionByRealms->add(0);
        self::assertSame($same, $additionByRealms);
        $increased = $additionByRealms->add(3);
        self::assertSame(0, $additionByRealms->getCurrentAddition(), 'Original addition should still has a zero current');
        self::assertNotSame($additionByRealms, $increased);
        self::assertSame(3, $increased->getValue());
        self::assertSame('3 {1=>5}', (string)$increased);
    }

    /**
     * @test
     */
    public function I_can_decrease_current_addition()
    {
        $additionByRealms = new AdditionByRealms(5);
        self::assertSame(5, $additionByRealms->getAdditionStep());
        self::assertSame(0, $additionByRealms->getCurrentAddition());
        self::assertSame('0 {1=>5}', (string)$additionByRealms);
        $same = $additionByRealms->sub(0);
        self::assertSame($same, $additionByRealms);
        $increased = $additionByRealms->sub(7);
        self::assertSame(0, $additionByRealms->getCurrentAddition(), 'Original addition should still has a zero current');
        self::assertNotSame($additionByRealms, $increased);
        self::assertSame(-7, $increased->getValue());
        self::assertSame('-7 {1=>5}', (string)$increased);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     */
    public function I_can_not_create_it_without_value()
    {
        new AdditionByRealms('');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     * @expectedExceptionMessageRegExp ~1=2=3~
     */
    public function I_can_not_create_it_with_too_many_parts()
    {
        new AdditionByRealms('1=2=3');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     */
    public function I_can_not_create_it_with_empty_realm_price()
    {
        new AdditionByRealms('=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfRealmsIncrement
     * @expectedExceptionMessageRegExp ~foo~
     */
    public function I_can_not_create_it_with_invalid_realm_price()
    {
        new AdditionByRealms('foo=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     * @expectedExceptionMessageRegExp ~5=~
     */
    public function I_can_not_create_it_with_empty_addition()
    {
        new AdditionByRealms('5=');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     * @expectedExceptionMessageRegExp ~bar~
     */
    public function I_can_not_create_it_with_invalid_addition()
    {
        new AdditionByRealms('13=bar');
    }
}