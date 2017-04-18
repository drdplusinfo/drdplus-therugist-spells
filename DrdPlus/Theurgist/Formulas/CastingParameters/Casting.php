<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use Granam\Integer\PositiveIntegerObject;

class Casting extends PositiveIntegerObject
{
    /**
     * @var TimeBonus
     */
    private $castingTimeBonus;

    /**
     * @param $value
     * @param TimeTable $timeTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForPositiveCastingParameter
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
        $this->castingTimeBonus = new TimeBonus($this->getValue(), $timeTable);
    }

    /**
     * @return TimeBonus
     */
    public function getCastingTimeBonus(): TimeBonus
    {
        return $this->castingTimeBonus;
    }

}