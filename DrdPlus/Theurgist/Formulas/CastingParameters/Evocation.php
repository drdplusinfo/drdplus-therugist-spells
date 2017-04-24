<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use Granam\Integer\PositiveIntegerObject;

class Evocation extends PositiveIntegerObject
{
    /**
     * @var Time
     */
    private $castingTime;

    /**
     * @param $value
     * @param TimeTable $timeTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Tables\Measurements\Time\Exceptions\CanNotConvertThatBonusToTime
     */
    public function __construct($value, TimeTable $timeTable)
    {
        try {
            parent::__construct($value);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForPositiveCastingParameter(
                'Expected positive integer: ' . $exception->getMessage()
            );
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->castingTime = (new TimeBonus($this->getValue(), $timeTable))->getTime();
    }

    /**
     * @return Time
     */
    public function getCastingTime(): Time
    {
        return $this->castingTime;
    }

}