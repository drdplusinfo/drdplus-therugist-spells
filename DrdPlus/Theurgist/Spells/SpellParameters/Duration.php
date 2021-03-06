<?php
declare(strict_types = 1);

namespace DrdPlus\Theurgist\Spells\SpellParameters;

use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\PositiveCastingParameter;

/**
 * @method Duration getWithAddition($additionValue)
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