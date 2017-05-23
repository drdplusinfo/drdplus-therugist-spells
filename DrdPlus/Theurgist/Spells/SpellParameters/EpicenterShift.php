<?php
namespace DrdPlus\Theurgist\Spells\SpellParameters;

use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\CastingParameter;

/**
 * @method EpicenterShift getWithAddition($additionValue)
 */
class EpicenterShift extends CastingParameter
{
    /**
     * @var Distance
     */
    private $distance;

    /**
     * @param array $values
     * @param Distance|null $distance to provide more accurate distance
     * @throws \LogicException
     */
    public function __construct(array $values, Distance $distance = null)
    {
        parent::__construct($values);
        if ($distance !== null) {
            if ($distance->getBonus()->getValue() !== $this->getValue()) {
                throw new \LogicException();
            }
            $this->distance = $distance;
        }
    }

    /**
     * @param DistanceTable $distanceTable
     * @return Distance
     */
    public function getDistance(DistanceTable $distanceTable): Distance
    {
        if ($this->distance === null) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->distance = (new DistanceBonus($this->getValue(), $distanceTable))->getDistance();
        }

        return $this->distance;
    }
}