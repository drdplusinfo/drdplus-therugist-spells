<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters\Partials;

use Granam\Integer\PositiveInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Tools\ValueDescriber;

abstract class PositiveCastingParameter extends IntegerCastingParameter implements PositiveInteger
{
    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Partials\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyNotation
     */
    public function __construct(array $values)
    {
        try {
            $values[0] = ToInteger::toPositiveInteger($values[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForPositiveCastingParameter(
                "Expected positive integer for {$this->getParameterName()}, got "
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0]) : 'nothing')
            );
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        parent::__construct($values);
    }
}