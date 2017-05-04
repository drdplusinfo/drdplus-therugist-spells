<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByDifficulty;
use Granam\Tests\Tools\TestWithMockery;

class AdditionByDifficultyTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it_with_just_an_addition()
    {
        $additionByDifficulty = new AdditionByDifficulty('123');
        self::assertSame(123, $additionByDifficulty->getAdditionStep());
        self::assertSame(1, $additionByDifficulty->getDifficultyOfAdditionStep());
        self::assertSame(0, $additionByDifficulty->getCurrentAddition());
        self::assertSame(0, $additionByDifficulty->getCurrentDifficultyIncrement());
        self::assertSame('0 {1=>123}', (string)$additionByDifficulty);

        $sameAdditionByDifficulty = new AdditionByDifficulty('1=123');
        self::assertSame(123, $sameAdditionByDifficulty->getAdditionStep());
        self::assertSame(1, $sameAdditionByDifficulty->getDifficultyOfAdditionStep());
        self::assertSame(0, $sameAdditionByDifficulty->getCurrentAddition());
        self::assertSame(0, $sameAdditionByDifficulty->getCurrentDifficultyIncrement());
        self::assertSame('0 {1=>123}', (string)$additionByDifficulty);
    }

    /**
     * @test
     */
    public function I_can_create_it_with_realms_price()
    {
        $additionByDifficulty = new AdditionByDifficulty('456=789');
        self::assertSame(789, $additionByDifficulty->getAdditionStep());
        self::assertSame(456, $additionByDifficulty->getDifficultyOfAdditionStep());
        self::assertSame(0, $additionByDifficulty->getCurrentAddition());
        self::assertSame(0, $additionByDifficulty->getCurrentDifficultyIncrement());
        self::assertSame('0 {456=>789}', (string)$additionByDifficulty);
    }

    /**
     * @test
     */
    public function I_can_create_it_with_custom_current_addition()
    {
        $additionByDifficulty = new AdditionByDifficulty('2=3', 7);
        self::assertSame(3, $additionByDifficulty->getAdditionStep());
        self::assertSame(2, $additionByDifficulty->getDifficultyOfAdditionStep());
        self::assertSame(7, $additionByDifficulty->getCurrentAddition());
        self::assertSame(5 /* 7 / 3 * 2, round up */, $additionByDifficulty->getCurrentDifficultyIncrement());
        self::assertSame('7 {2=>3}', (string)$additionByDifficulty);
    }

    /**
     * @test
     */
    public function I_can_increase_current_addition()
    {
        $additionByDifficulty = new AdditionByDifficulty(5);
        self::assertSame(5, $additionByDifficulty->getAdditionStep());
        self::assertSame(0, $additionByDifficulty->getCurrentAddition());
        self::assertSame('0 {1=>5}', (string)$additionByDifficulty);
        $same = $additionByDifficulty->add(0);
        self::assertSame($same, $additionByDifficulty);
        $increased = $additionByDifficulty->add(3);
        self::assertSame(0, $additionByDifficulty->getCurrentAddition(), 'Original addition should still has a zero current');
        self::assertNotSame($additionByDifficulty, $increased);
        self::assertSame(3, $increased->getValue());
        self::assertSame('3 {1=>5}', (string)$increased);
    }

    /**
     * @test
     */
    public function I_can_decrease_current_addition()
    {
        $additionByDifficulty = new AdditionByDifficulty(5);
        self::assertSame(5, $additionByDifficulty->getAdditionStep());
        self::assertSame(0, $additionByDifficulty->getCurrentAddition());
        self::assertSame('0 {1=>5}', (string)$additionByDifficulty);
        $same = $additionByDifficulty->sub(0);
        self::assertSame($same, $additionByDifficulty);
        $increased = $additionByDifficulty->sub(7);
        self::assertSame(0, $additionByDifficulty->getCurrentAddition(), 'Original addition should still has a zero current');
        self::assertNotSame($additionByDifficulty, $increased);
        self::assertSame(-7, $increased->getValue());
        self::assertSame('-7 {1=>5}', (string)$increased);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByDifficulty
     */
    public function I_can_not_create_it_without_value()
    {
        new AdditionByDifficulty('');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByDifficulty
     * @expectedExceptionMessageRegExp ~1=2=3~
     */
    public function I_can_not_create_it_with_too_many_parts()
    {
        new AdditionByDifficulty('1=2=3');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByDifficulty
     */
    public function I_can_not_create_it_with_empty_realm_price()
    {
        new AdditionByDifficulty('=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     * @expectedExceptionMessageRegExp ~foo~
     */
    public function I_can_not_create_it_with_invalid_realm_price()
    {
        new AdditionByDifficulty('foo=2');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByDifficulty
     * @expectedExceptionMessageRegExp ~5=~
     */
    public function I_can_not_create_it_with_empty_addition()
    {
        new AdditionByDifficulty('5=');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     * @expectedExceptionMessageRegExp ~bar~
     */
    public function I_can_not_create_it_with_invalid_addition()
    {
        new AdditionByDifficulty('13=bar');
    }
}