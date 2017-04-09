<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Codes\AffectionPeriodCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use Granam\Tests\Tools\TestWithMockery;

class AffectionTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_create_it_without_period()
    {
        $affection = new Affection(['-213']);
        self::assertSame(-213, $affection->getValue());
        self::assertSame('-213 daily', (string)$affection);
        self::assertSame(AffectionPeriodCode::getIt(AffectionPeriodCode::DAILY), $affection->getAffectionPeriod());
    }

    /**
     * @test
     */
    public function I_can_create_it_with_explicit_period()
    {
        $affection = new Affection(['-357', AffectionPeriodCode::MONTHLY]);
        self::assertSame(-357, $affection->getValue());
        self::assertSame('-357 monthly', (string)$affection);
        self::assertSame(AffectionPeriodCode::getIt(AffectionPeriodCode::MONTHLY), $affection->getAffectionPeriod());
    }

    /**
     * @test
     */
    public function I_can_create_it_zero_affection()
    {
        $affection = new Affection(['0']);
        self::assertSame(0, $affection->getValue());
        self::assertSame('0', (string)$affection);
        self::assertSame(AffectionPeriodCode::getIt(AffectionPeriodCode::DAILY), $affection->getAffectionPeriod());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatForNegativeCastingParameter
     * @expectedExceptionMessageRegExp ~1~
     */
    public function I_can_not_create_it_positive_affection()
    {
        new Affection(['1']);
    }

}