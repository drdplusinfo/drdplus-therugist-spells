<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;

class FormulasTable extends AbstractFileTable
{
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/formulas.csv';
    }

    const MODIFIERS = 'modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::MODIFIERS => self::ARRAY,
        ];
    }

    const FORMULA = 'formula';

    protected function getRowsHeader(): array
    {
        return [
            self::FORMULA,
        ];
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|ModifierCode[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetModifiersFor
     */
    public function getModifiersForFormula(FormulaCode $formulaCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $modifierName) {
                    return ModifierCode::getIt($modifierName);
                },
                $this->getValue($formulaCode, self::MODIFIERS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownFormulaToGetModifiersFor(
                "Given formula code '{$formulaCode}' is unknown"
            );
        }
    }

}