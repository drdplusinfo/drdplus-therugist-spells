<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\PositiveInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Tools\ValueDescriber;

abstract class PositiveCastingParameter extends IntegerCastingParameter implements PositiveInteger
{
    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForPositiveCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
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