<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Codes\TraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use Granam\Tests\Tools\TestWithMockery;

class SpellTraitTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $implicitSpellTrait = new SpellTrait(TraitCode::ACTIVE);
        self::assertSame(TraitCode::getIt(TraitCode::ACTIVE), $implicitSpellTrait->getTraitCode());
        self::assertSame(1, $implicitSpellTrait->getDifficultyChange());

        $explicitSpellTrait = new SpellTrait(TraitCode::BIDIRECTIONAL . '=123');
        self::assertSame(TraitCode::getIt(TraitCode::BIDIRECTIONAL), $explicitSpellTrait->getTraitCode());
        self::assertSame(123, $explicitSpellTrait->getDifficultyChange());

        $negativeSpellTrait = new SpellTrait(TraitCode::NATURE_CHANGE . '=-456');
        self::assertSame(TraitCode::getIt(TraitCode::NATURE_CHANGE), $negativeSpellTrait->getTraitCode());
        self::assertSame(-456, $negativeSpellTrait->getDifficultyChange());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForSpellTraitDifficultyChange
     * @expectedExceptionMessageRegExp ~impossible~
     */
    public function I_can_not_create_it_with_non_number_difficulty_change()
    {
        new SpellTrait(TraitCode::BIDIRECTIONAL . '=impossible');
    }
}