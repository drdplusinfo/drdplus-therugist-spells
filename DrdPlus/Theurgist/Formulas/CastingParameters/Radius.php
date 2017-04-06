<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\Tools\ToInteger;
use Granam\Tools\ValueDescriber;

class Radius extends CastingParameter
{
    private $radius;

    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForRadius
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForAdditionByRealm
     */
    public function __construct(array $values)
    {
        try {
            $this->radius = ToInteger::toInteger($values[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForRadius(
                'Expected integer for radius, got '
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0], true) : 'nothing')
            );
        }
        parent::__construct($values, 1);
    }

    /**
     * @return int
     */
    public function getRadius(): int
    {
        return $this->radius;
    }
}