<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyLimit;
use Granam\Tests\Tools\TestWithMockery;

class DifficultyTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $zeroMinimalDifficulty = new DifficultyLimit(['0', '65', '12=13']);
        self::assertSame(0, $zeroMinimalDifficulty->getMinimal());
        self::assertSame(65, $zeroMinimalDifficulty->getMaximal());
        self::assertEquals(new AdditionByRealms('12=13'), $zeroMinimalDifficulty->getAdditionByRealms());
        self::assertSame('0 - 65 (12=>13)', (string)$zeroMinimalDifficulty);

        $sameMinimalAsMaximal = new DifficultyLimit(['89', '89', '1=2']);
        self::assertSame(89, $sameMinimalAsMaximal->getMinimal());
        self::assertSame(89, $sameMinimalAsMaximal->getMaximal());
        self::assertSame('89 (1=>2)', (string)$sameMinimalAsMaximal);

        $withoutAdditionByRealms = new DifficultyLimit(['123', '456', '0']);
        self::assertSame(123, $withoutAdditionByRealms->getMinimal());
        self::assertSame(456, $withoutAdditionByRealms->getMaximal());
        self::assertSame('123 - 456', (string)$withoutAdditionByRealms);

        $simplyZero = new DifficultyLimit(['0', '0', '0']);
        self::assertSame(0, $simplyZero->getMinimal());
        self::assertSame(0, $simplyZero->getMaximal());
        self::assertSame('0', (string)$simplyZero);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMinimalDifficulty
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_create_it_with_negative_minimum()
    {
        new DifficultyLimit(['-1', '65', '12=13']);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMaximalDifficulty
     * @expectedExceptionMessageRegExp ~-15~
     */
    public function I_can_not_create_it_with_negative_maximum()
    {
        new DifficultyLimit(['6', '-15', '12=13']);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal
     * @expectedExceptionMessageRegExp ~12.+11~
     */
    public function I_can_not_create_it_with_lesser_maximum_than_minimum()
    {
        new DifficultyLimit(['12', '11', '12=13']);
    }
}