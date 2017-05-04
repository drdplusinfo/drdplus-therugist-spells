<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Spells\CastingParameters\Partials\PositiveCastingParameter;

/**
 * @method Evocation setAddition($additionValue)
 */
class Evocation extends PositiveCastingParameter
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