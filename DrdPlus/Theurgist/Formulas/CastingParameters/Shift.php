<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;

class Shift extends IntegerCastingParameter
{
    /**
     * @var DistanceBonus
     */
    private $distance;

    /**
     * @param array $values
     * @param DistanceTable $distanceTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForIntegerCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(array $values, DistanceTable $distanceTable)
    {
        parent::__construct($values);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->distance = new DistanceBonus($this->getValue(), $distanceTable);
    }

    /**
     * @return DistanceBonus
     */
    public function getDistance(): DistanceBonus
    {
        return $this->distance;
    }
}