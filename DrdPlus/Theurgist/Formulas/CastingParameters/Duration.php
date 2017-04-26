<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\PositiveCastingParameter;

/**
 * @method Duration add($value)
 * @method Duration sub($value)
 */
class Duration extends PositiveCastingParameter
{
    /**
     * @param TimeTable $timeTable
     * @return Time
     * @throws \DrdPlus\Tables\Measurements\Time\Exceptions\CanNotConvertThatBonusToTime
     */
    public function getDurationTime(TimeTable $timeTable): Time
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (new TimeBonus($this->getValue(), $timeTable))->getTime();
    }

}