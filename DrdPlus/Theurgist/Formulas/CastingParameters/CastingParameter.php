<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

/** @noinspection SingletonFactoryPatternViolationInspection */
abstract class CastingParameter extends StrictObject
{
    protected $additionByRealm;

    /**
     * @param array $values
     * @param int $index
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForAdditionByRealm
     */
    protected function __construct(array $values, int $index)
    {
        try {
            $this->additionByRealm = ToInteger::toPositiveInteger($values[$index] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForAdditionByRealm(
                'Expected positive integer for addition by realm, got '
                . (array_key_exists($index, $values) ? ValueDescriber::describe($values[$index], true) : 'nothing')
            );
        }
    }

    /**
     * @return int
     */
    public function getAdditionByRealm(): int
    {
        return $this->additionByRealm;
    }
}