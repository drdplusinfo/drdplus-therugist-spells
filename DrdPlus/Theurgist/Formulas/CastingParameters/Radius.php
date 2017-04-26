<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\IntegerCastingParameter;

/**
 * @method Radius add($value)
 * @method Radius sub($value)
 */
class Radius extends IntegerCastingParameter
{
    /**
     * @param DistanceTable $distanceTable
     * @return Distance
     */
    public function getDistance(DistanceTable $distanceTable): Distance
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (new DistanceBonus($this->getValue(), $distanceTable))->getDistance();
    }
}