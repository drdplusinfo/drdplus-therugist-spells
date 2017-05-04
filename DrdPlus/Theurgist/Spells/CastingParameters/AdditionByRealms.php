<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

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
    private $realmsOfAdditionStep;
    /**
     * @var int
     */
    private $additionStep;
    /**
     * @var int
     */
    private $currentAddition;

    /**
     * @param string $additionByRealmsNotation in format 'number' or 'number=number'
     * @param int|null $currentAddition How much is currently active addition
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfRealmsIncrement
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     */
    public function __construct(string $additionByRealmsNotation, int $currentAddition = null)
    {
        $parts = $this->parseParts($additionByRealmsNotation);
        if (count($parts) === 1 && array_keys($parts) === [0]) {
            $this->realmsOfAdditionStep = 1;
            $this->additionStep = $this->sanitizeAddition($parts[0]);
        } else if (count($parts) === 2 && array_keys($parts) === [0, 1]) {
            $this->realmsOfAdditionStep = $this->sanitizeRealms($parts[0]);
            $this->additionStep = $this->sanitizeAddition($parts[1]);
        } else {
            throw new Exceptions\InvalidFormatOfAdditionByRealmsNotation(
                "Expected addition by realms in format 'number' or 'number=number', got "
                . ValueDescriber::describe($additionByRealmsNotation)
            );
        }
        $this->currentAddition = $currentAddition ?? 0;/* no addition, no realm increment */
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
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfRealmsIncrement
     */
    private function sanitizeRealms($realmIncrement): int
    {
        try {
            return ToInteger::toPositiveInteger($realmIncrement);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfRealmsIncrement(
                'Expected positive integer for realms increment , got ' . ValueDescriber::describe($realmIncrement)
            );
        }
    }

    /**
     * @param $addition
     * @return int
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     */
    private function sanitizeAddition($addition): int
    {
        try {
            return ToInteger::toInteger($addition);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfAdditionByRealmsValue(
                'Expected integer for addition by realm, got ' . ValueDescriber::describe($addition)
            );
        }
    }

    /**
     * How is realms increased on addition step, @see getAdditionStep.
     *
     * @return int
     */
    public function getRealmsOfAdditionStep(): int
    {
        return $this->realmsOfAdditionStep;
    }

    /**
     * Bonus given by increasing realms, @see getRealmsOfAdditionStep
     *
     * @return int
     */
    public function getAdditionStep(): int
    {
        return $this->additionStep;
    }

    /**
     * Current value of a bonus paid by realms, steps x @see getAdditionStep
     *
     * @return int
     */
    public function getCurrentAddition(): int
    {
        return $this->currentAddition;
    }

    /**
     * How much have to be realms increased to get total bonus, steps x @see getCurrentAddition
     *
     * @return int
     */
    public function getCurrentRealmsIncrement(): int
    {
        return ceil($this->getCurrentAddition() / $this->getAdditionStep() * $this->getRealmsOfAdditionStep());
    }

    /**
     * Same as @see getCurrentAddition (representing current value of an Integer object)
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->getCurrentAddition();
    }

    /**
     * @param int|float|NumberInterface $value
     * @return AdditionByRealms
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     */
    public function add($value): AdditionByRealms
    {
        $value = $this->sanitizeAddition($value);
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
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     */
    public function sub($value): AdditionByRealms
    {
        $value = $this->sanitizeAddition($value);
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
        return "{$this->getValue()} {{$this->getRealmsOfAdditionStep()}=>{$this->getAdditionStep()}}";
    }

    /**
     * @return string
     */
    public function getNotation(): string
    {
        return "{$this->getRealmsOfAdditionStep()}={$this->getAdditionStep()}";
    }
}