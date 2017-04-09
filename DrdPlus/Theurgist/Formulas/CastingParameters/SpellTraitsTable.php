<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\Properties\PropertyCode;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\TraitCode;

class SpellTraitsTable extends AbstractFileTable
{
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/spell_traits.csv';
    }

    const FORMULAS = 'formulas';
    const MODIFIERS = 'modifiers';
    const DIFFICULTY = 'difficulty';
    const TRAP = 'trap';
    const TRAP_SENSE = 'trap_sense';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::FORMULAS => self::ARRAY,
            self::MODIFIERS => self::ARRAY,
            self::DIFFICULTY => self::INTEGER,
            self::TRAP => self::ARRAY,
            self::TRAP_SENSE => self::STRING,
        ];
    }

    const TRAIT = 'trait';

    protected function getRowsHeader(): array
    {
        return [self::TRAIT];
    }

    /**
     * @param TraitCode $traitCode
     * @return array
     */
    public function getFormulas(TraitCode $traitCode): array
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
     * @param TraitCode $traitCode
     * @return array
     */
    public function getModifiers(TraitCode $traitCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $modifierValue) {
                return FormulaCode::getIt($modifierValue);
            },
            $this->getValue($traitCode, self::MODIFIERS)
        );
    }

    /**
     * @param TraitCode $traitCode
     * @return int
     */
    public function getDifficulty(TraitCode $traitCode): int
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->getValue($traitCode, self::DIFFICULTY);
    }

    /**
     * @param TraitCode $traitCode
     * @return Trap
     */
    public function getTrap(TraitCode $traitCode): Trap
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $trapValues = $this->getValue($traitCode, self::TRAP);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $trapSense = $this->getValue($traitCode, self::TRAP_SENSE);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Trap($trapValues, PropertyCode::getIt($trapSense));
    }

}