<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class AdditionByRealm extends StrictObject
{
    private $realmsNumber;
    private $addition;

    /**
     * @param string $additionByRealmNotation in format 'number' or 'number=number'
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmsNumber
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAddition
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(string $additionByRealmNotation)
    {
        $parts = $this->parseParts($additionByRealmNotation);
        if (count($parts) === 1) {
            $this->realmsNumber = 1;
            $this->addition = $this->sanitizeAddition($parts[0]);
        } else if (count($parts) === 2) {
            $this->realmsNumber = $this->sanitizeRealmsNumber($parts[0]);
            $this->addition = $this->sanitizeAddition($parts[1]);
        } else {
            throw new Exceptions\UnexpectedFormatOfAdditionByRealm(
                "Expected format of addition by realm as 'number' or 'number=number', got "
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

        return array_filter(
            $parts,
            function (string $part) {
                return $part !== '';
            }
        );
    }

    /**
     * @param $realmsNumber
     * @return int
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmsNumber
     */
    private function sanitizeRealmsNumber($realmsNumber): int
    {
        try {
            return ToInteger::toPositiveInteger($realmsNumber);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfRealmsNumber(
                'For \'realms\' number expected positive integer, got ' . ValueDescriber::describe($realmsNumber)
            );
        }
    }

    /**
     * @param $addition
     * @return int
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAddition
     */
    private function sanitizeAddition($addition): int
    {
        try {
            return ToInteger::toInteger($addition);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfAddition(
                'For \'addition\' expected integer, got ' . ValueDescriber::describe($addition)
            );
        }
    }

    /**
     * @return int
     */
    public function getRealmsNumber(): int
    {
        return $this->realmsNumber;
    }

    /**
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
        return "{$this->getRealmsNumber()}=>{$this->getAddition()}";
    }

}