<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameter;

/**
 * @method CastingRounds setAddition($additionValue)
 */
class CastingRounds extends PositiveCastingParameter
{
    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByDifficulty
     */
    public function __construct(array $values)
    {
        if (!array_key_exists(1, $values)) {
            $values[1] = 0; // no addition by realms
        }
        parent::__construct($values);
    }
}