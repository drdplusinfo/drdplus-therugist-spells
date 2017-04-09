<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;

class Duration extends PositiveCastingParameter
{
    /**
     * @var TimeBonus
     */
    private $duration;

    /**
     * @param array $values
     * @param TimeTable $timeTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(array $values, TimeTable $timeTable)
    {
        parent::__construct($values);
        $this->duration = new TimeBonus($this->getValue(), $timeTable);
    }

    /**
     * @return TimeBonus
     */
    public function getDuration(): TimeBonus
    {
        return $this->duration;
    }

}