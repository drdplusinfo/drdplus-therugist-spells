<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;

/**
 * @method EpicenterShift add($value)
 * @method EpicenterShift sub($value)
 */
class EpicenterShift extends IntegerCastingParameter
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