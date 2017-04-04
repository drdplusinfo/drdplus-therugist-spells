<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;

class ProfilesTable extends AbstractFileTable
{
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/profiles.csv';
    }

    const MODIFIERS = 'modifiers';
    const FORMULAS = 'formulas';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::MODIFIERS => self::ARRAY,
            self::FORMULAS => self::ARRAY,
        ];
    }

    const PROFILE = 'profile';

    protected function getRowsHeader(): array
    {
        return [
            self::PROFILE,
        ];
    }

    /**
     * @param ProfileCode $profileCode
     * @return array|ModifierCode[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownProfileToGetModifiersFor
     */
    public function getModifiersForProfile(ProfileCode $profileCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $modifierValue) {
                    return ModifierCode::getIt($modifierValue);
                },
                $this->getValue($profileCode, self::MODIFIERS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownProfileToGetModifiersFor("Given profile code '{$profileCode}' is unknown");
        }
    }

    /**
     * @param ProfileCode $profileCode
     * @return array|FormulaCode[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownProfileToGetFormulasFor
     */
    public function getFormulasForProfile(ProfileCode $profileCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $formulaValue) {
                    return FormulaCode::getIt($formulaValue);
                },
                $this->getValue($profileCode, self::FORMULAS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownProfileToGetFormulasFor("Given profile code '{$profileCode}' is unknown");
        }
    }

}