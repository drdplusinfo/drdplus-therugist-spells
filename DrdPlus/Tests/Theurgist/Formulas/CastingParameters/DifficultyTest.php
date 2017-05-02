<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tests\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameterTest;
use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\Difficulty;

class DifficultyTest extends PositiveCastingParameterTest
{

    protected function I_can_create_it_with_zero()
    {
        $difficulty = new Difficulty(['0', '1', '78=321']);
        self::assertSame(0, $difficulty->getValue());
        self::assertEquals(new AdditionByRealms('78=321'), $difficulty->getAdditionByRealms());
        self::assertSame('0 (0...1 [' . $difficulty->getAdditionByRealms() . '])', (string)$difficulty);
    }

    protected function I_can_create_it_positive()
    {
        $difficulty = new Difficulty(['35689', '356891', '332211']);
        self::assertSame(35689, $difficulty->getValue());
        self::assertEquals(new AdditionByRealms('332211'), $difficulty->getAdditionByRealms());
        self::assertSame('35689 (35689...356891 [' . $difficulty->getAdditionByRealms() . '])', (string)$difficulty);
    }

    /**
     * @test
     */
    public function I_can_not_create_it_non_numeric()
    {
        self::assertTrue(true); // this is solved by Difficulty itself as 'minimal' and 'maximal' values check
    }

    /**
     * @test
     */
    public function I_can_not_create_it_negative()
    {
        self::assertTrue(true); // this is solved by Difficulty itself as 'minimal' and 'maximal' values check
    }

    /**
     * @test
     */
    public function I_can_get_its_clone_changed_by_addition()
    {
        $original = new Difficulty(['123', '345', '456=789']);
        $increased = $original->setAddition(456);
        self::assertSame(579, $increased->getValue());
        self::assertSame($original->getAdditionByRealms()->getNotation(), $increased->getAdditionByRealms()->getNotation());
        self::assertSame(456, $increased->getAdditionByRealms()->getCurrentAddition());
        self::assertNotSame($original, $increased);

        $zeroed = $increased->setAddition(-123);
        self::assertSame(0, $zeroed->getValue());
        self::assertNotSame($original, $zeroed);
        self::assertNotSame($original, $increased);
        self::assertSame(-123, $zeroed->getAdditionByRealms()->getCurrentAddition());
        self::assertSame($original->getAdditionByRealms()->getNotation(), $zeroed->getAdditionByRealms()->getNotation());

        $decreased = $zeroed->setAddition(-234);
        self::assertSame(-111, $decreased->getValue());
        self::assertSame($zeroed->getAdditionByRealms()->getNotation(), $decreased->getAdditionByRealms()->getNotation());
        self::assertSame(-234, $decreased->getAdditionByRealms()->getCurrentAddition());
        self::assertNotSame($zeroed, $decreased);
    }

    /**
     * @test
     */
    public function I_can_use_it()
    {
        $zeroMinimalDifficulty = new Difficulty(['0', '65', '12=13']);
        self::assertSame(0, $zeroMinimalDifficulty->getMinimal());
        self::assertSame(65, $zeroMinimalDifficulty->getMaximal());
        self::assertEquals(new AdditionByRealms('12=13'), $zeroMinimalDifficulty->getAdditionByRealms());
        self::assertSame('0 (0...65 [0 {12=>13}])', (string)$zeroMinimalDifficulty);

        $sameMinimalAsMaximal = new Difficulty(['89', '89', '1=2']);
        self::assertSame(89, $sameMinimalAsMaximal->getMinimal());
        self::assertSame(89, $sameMinimalAsMaximal->getMaximal());
        self::assertSame('89 (89...89 [0 {1=>2}])', (string)$sameMinimalAsMaximal);

        $withoutAdditionByRealms = new Difficulty(['123', '456', '0']);
        self::assertSame(123, $withoutAdditionByRealms->getMinimal());
        self::assertSame(456, $withoutAdditionByRealms->getMaximal());
        self::assertSame('123 (123...456 [0 {1=>0}])', (string)$withoutAdditionByRealms);

        $simplyZero = new Difficulty(['0', '0', '0']);
        self::assertSame(0, $simplyZero->getMinimal());
        self::assertSame(0, $simplyZero->getMaximal());
        self::assertSame('0 (0...0 [0 {1=>0}])', (string)$simplyZero);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMinimalDifficulty
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_create_it_with_negative_minimum()
    {
        new Difficulty(['-1', '65', '12=13']);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMaximalDifficulty
     * @expectedExceptionMessageRegExp ~-15~
     */
    public function I_can_not_create_it_with_negative_maximum()
    {
        new Difficulty(['6', '-15', '12=13']);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal
     * @expectedExceptionMessageRegExp ~12.+11~
     */
    public function I_can_not_create_it_with_lesser_maximum_than_minimum()
    {
        new Difficulty(['12', '11', '12=13']);
    }
}