<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class AdditionByRealms extends StrictObject
{
    private $realmIncrement;
    private $addition;

    /**
     * @param string $additionByRealmNotation in format 'number' or 'number=number'
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(string $additionByRealmNotation)
    {
        $parts = $this->parseParts($additionByRealmNotation);
        if (count($parts) === 1 && array_keys($parts) === [0]) {
            $this->realmIncrement = 1;
            $this->addition = $this->sanitizeAddition($parts[0]);
        } else if (count($parts) === 2 && array_keys($parts) === [0, 1]) {
            $this->realmIncrement = $this->sanitizeRealmIncrement($parts[0]);
            $this->addition = $this->sanitizeAddition($parts[1]);
        } else {
            throw new Exceptions\UnexpectedFormatOfAdditionByRealm(
                "Expected addition by realm in format 'number' or 'number=number', got "
                . ValueDescriber::describe($additionByRealmNotation)
            );
        }
    }

    /**
     * @param string $additionByRealmNotation
     * @return array|string[]
     */
    private function parseParts(string $additionByRealmNotation): array
    {
        $parts = array_map(
            function (string $part) {
                return trim($part);
            },
            explode('=', $additionByRealmNotation)
        );

        foreach ($parts as $part) {
            if ($part === '') {
                return [];
            }
        }

        return $parts;
    }

    /**
     * @param $realmIncrement
     * @return int
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     */
    private function sanitizeRealmIncrement($realmIncrement): int
    {
        try {
            return ToInteger::toPositiveInteger($realmIncrement);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfRealmIncrement(
                'Expected positive integer for realm increment , got ' . ValueDescriber::describe($realmIncrement)
            );
        }
    }

    /**
     * @param $addition
     * @return int
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     */
    private function sanitizeAddition($addition): int
    {
        try {
            return ToInteger::toInteger($addition);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfAdditionByRealmValue(
                'Expected integer for addition , got ' . ValueDescriber::describe($addition)
            );
        }
    }

    /**
     * How much more realms are need to get addition, @see getAddition.
     *
     * @return int
     */
    public function getRealmIncrement(): int
    {
        return $this->realmIncrement;
    }

    /**
     * Bonus given by using realm incremented by given level, @see getRealmIncrement.
     *
     * @return int
     */
    public function getAddition(): int
    {
        return $this->addition;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getRealmIncrement()}=>{$this->getAddition()}";
    }

}