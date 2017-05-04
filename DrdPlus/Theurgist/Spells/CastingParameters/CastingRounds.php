<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use DrdPlus\Theurgist\Spells\CastingParameters\Partials\PositiveCastingParameter;

/**
 * @method CastingRounds setAddition($additionValue)
 */
class CastingRounds extends PositiveCastingParameter
{
    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Partials\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyNotation
     */
    public function __construct(array $values)
    {
        if (!array_key_exists(1, $values)) {
            $values[1] = 0; // no addition by realms
        }
        parent::__construct($values);
    }
}