<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class AdditionByRealms extends StrictObject implements IntegerInterface
{
    /**
     * @var int
     */
    private $realmIncrementPerAddition;
    /**
     * @var int
     */
    private $defaultAddition;
    /**
     * @var int
     */
    private $currentAddition;

    /**
     * @param string $additionByRealmNotation in format 'number' or 'number=number'
     * @param int|null $currentAddition How much is currently active addition
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(string $additionByRealmNotation, int $currentAddition = null)
    {
        $parts = $this->parseParts($additionByRealmNotation);
        if (count($parts) === 1 && array_keys($parts) === [0]) {
            $this->realmIncrementPerAddition = 1;
            $this->defaultAddition = $this->sanitizeAddition($parts[0]);
        } else if (count($parts) === 2 && array_keys($parts) === [0, 1]) {
            $this->realmIncrementPerAddition = $this->sanitizeRealmIncrement($parts[0]);
            $this->defaultAddition = $this->sanitizeAddition($parts[1]);
        } else {
            throw new Exceptions\UnexpectedFormatOfAdditionByRealm(
                "Expected addition by realm in format 'number' or 'number=number', got "
                . ValueDescriber::describe($additionByRealmNotation)
            );
        }
        $this->currentAddition = $currentAddition ?? $this->defaultAddition;
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
     * How much more realms are need to get addition, @see getDefaultAddition.
     *
     * @return int
     */
    public function getRealmIncrementPerAddition(): int
    {
        return $this->realmIncrementPerAddition;
    }

    /**
     * Bonus given by using realm incremented by a given level, @see getRealmIncrementPerAddition
     *
     * @return int
     */
    public function getDefaultAddition(): int
    {
        return $this->defaultAddition;
    }

    /**
     * Current value of a bonus given by increased realm
     *
     * @return int
     */
    public function getCurrentAddition(): int
    {
        return $this->currentAddition;
    }

    /**
     * How much more realms are need to get total bonus, @see getCurrentAddition
     *
     * @return int
     */
    public function getCurrentRealmIncrement(): int
    {
        return ceil($this->getCurrentAddition() / $this->getDefaultAddition() * $this->getRealmIncrementPerAddition());
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->getCurrentAddition();
    }

    /**
     * @param int|float|NumberInterface $value
     * @return AdditionByRealms
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function add($value): AdditionByRealms
    {
        $value = ToInteger::toInteger($value);
        if ($value === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $this->getNotation(),
            $this->getValue() + ToInteger::toInteger($value) // current addition is injected as second parameter
        );
    }

    /**
     * @param int|float|NumberInterface $value
     * @return AdditionByRealms
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function sub($value): AdditionByRealms
    {
        $value = ToInteger::toInteger($value);
        if ($value === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $this->getNotation(),
            $this->getValue() - ToInteger::toInteger($value) // current addition is injected as second parameter
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getRealmIncrementPerAddition()}=>{$this->getDefaultAddition()}";
    }

    /**
     * @return string
     */
    public function getNotation(): string
    {
        return "{$this->getRealmIncrementPerAddition()}={$this->getDefaultAddition()}";
    }
}