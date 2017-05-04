<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameter;

/**
 * @method Evocation setAddition($additionValue)
 */
class Evocation extends PositiveCastingParameter
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

    /**
     * @param TimeTable $timeTable
     * @return Time
     * @throws \DrdPlus\Tables\Measurements\Time\Exceptions\CanNotConvertThatBonusToTime
     */
    public function getEvocationTime(TimeTable $timeTable): Time
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (new TimeBonus($this->getValue(), $timeTable))->getTime();
    }

}