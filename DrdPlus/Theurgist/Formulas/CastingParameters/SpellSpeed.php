<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tables\Measurements\Speed\Speed;
use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedTable;

class SpellSpeed extends IntegerCastingParameter
{
    /**
     * @var Speed
     */
    private $speed;

    /**
     * @param array $values
     * @param SpeedTable $speedTable
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForIntegerCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(array $values, SpeedTable $speedTable)
    {
        parent::__construct($values);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->speed = (new SpeedBonus($this->getValue(), $speedTable))->getSpeed();
    }

    /**
     * @return Speed
     */
    public function getSpeed(): Speed
    {
        return $this->speed;
    }
}