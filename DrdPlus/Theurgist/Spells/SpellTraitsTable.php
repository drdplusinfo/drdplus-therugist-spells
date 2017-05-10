<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\SpellParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\SpellParameters\Trap;

class SpellTraitsTable extends AbstractFileTable
{
    use ToFlatArrayTrait;

    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/spell_traits.csv';
    }

    const FORMULAS = 'formulas';
    const MODIFIERS = 'modifiers';
    const DIFFICULTY_CHANGE = 'difficulty_change';
    const TRAP = 'trap';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::FORMULAS => self::ARRAY,
            self::MODIFIERS => self::ARRAY,
            self::DIFFICULTY_CHANGE => self::INTEGER,
            self::TRAP => self::ARRAY,
        ];
    }

    const TRAIT = 'trait';

    protected function getRowsHeader(): array
    {
        return [self::TRAIT];
    }

    /**
     * @param SpellTraitCode $traitCode
     * @return array|FormulaCode[]
     */
    public function getFormulas(SpellTraitCode $traitCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $formulaValue) {
                return FormulaCode::getIt($formulaValue);
            },
            $this->getValue($traitCode, self::FORMULAS)
        );
    }

    /**
     * @param SpellTraitCode $traitCode
     * @return array|ModifierCode[]
     */
    public function getModifiers(SpellTraitCode $traitCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $modifierValue) {
                return ModifierCode::getIt($modifierValue);
            },
            $this->getValue($traitCode, self::MODIFIERS)
        );
    }

    /**
     * @param SpellTraitCode $spellTraitCode
     * @return DifficultyChange
     */
    public function getDifficultyChange(SpellTraitCode $spellTraitCode): DifficultyChange
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DifficultyChange($this->getValue($spellTraitCode, self::DIFFICULTY_CHANGE));
    }

    /**
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return DifficultyChange
     */
    public function sumDifficultyChanges(array $spellTraitCodes): DifficultyChange
    {
        $sumOfDifficultyChange = 0;
        foreach ($this->toFlatArray($spellTraitCodes) as $spellTraitCode) {
            $sumOfDifficultyChange += $this->getDifficultyChange($spellTraitCode)->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DifficultyChange($sumOfDifficultyChange);
    }

    /**
     * @param SpellTraitCode $traitCode
     * @return Trap|null
     */
    public function getTrap(SpellTraitCode $traitCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $trapValues = $this->getValue($traitCode, self::TRAP);
        if (count($trapValues) === 0) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Trap($trapValues);
    }

}