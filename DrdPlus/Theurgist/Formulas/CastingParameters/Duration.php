<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use Granam\Integer\Tools\ToInteger;
use Granam\Tools\ValueDescriber;

class Duration extends CastingParameter
{
    /**
     * @var TimeBonus
     */
    private $duration;

    /**
     * @param array $values
     * @param TimeTable $timeTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForDuration
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     */
    public function __construct(array $values, TimeTable $timeTable)
    {
        try {
            $this->duration = new TimeBonus(ToInteger::toPositiveInteger($values[0] ?? null), $timeTable);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForDuration(
                'Expected positive integer for duration, got '
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0], true) : 'nothing')
            );
        }
        parent::__construct($values, 1);
    }

    /**
     * @return TimeBonus
     */
    public function getDuration(): TimeBonus
    {
        return $this->duration;
    }
}