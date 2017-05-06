<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

/**
 * A bonus paid by difficulty increment
 */
class AdditionByDifficulty extends StrictObject implements IntegerInterface
{
    /**
     * @var int
     */
    private $difficultyOfAdditionStep;
    /**
     * @var int
     */
    private $additionStep;
    /**
     * @var int
     */
    private $currentAddition;

    /**
     * @param string $additionByDifficultyNotation in format 'number' or 'number=number'
     * @param int|null $currentAddition How much is currently active addition
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyNotation
     */
    public function __construct(string $additionByDifficultyNotation, int $currentAddition = null)
    {
        $parts = $this->parseParts($additionByDifficultyNotation);
        if (count($parts) === 1 && array_keys($parts) === [0]) {
            $this->difficultyOfAdditionStep = 1;
            $this->additionStep = $this->sanitizeAddition($parts[0]);
        } else if (count($parts) === 2 && array_keys($parts) === [0, 1]) {
            $this->difficultyOfAdditionStep = $this->sanitizeDifficulty($parts[0]);
            $this->additionStep = $this->sanitizeAddition($parts[1]);
        } else {
            throw new Exceptions\InvalidFormatOfAdditionByDifficultyNotation(
                "Expected addition by difficulty in format 'number' or 'number=number', got "
                . ValueDescriber::describe($additionByDifficultyNotation)
            );
        }
        $this->currentAddition = $currentAddition ?? 0;/* no addition, no difficulty change */
    }

    /**
     * @param string $additionByDifficultyNotation
     * @return array|string[]
     */
    private function parseParts(string $additionByDifficultyNotation): array
    {
        $parts = array_map(
            function (string $part) {
                return trim($part);
            },
            explode('=', $additionByDifficultyNotation)
        );

        foreach ($parts as $part) {
            if ($part === '') {
                return [];
            }
        }

        return $parts;
    }

    /**
     * @param $difficultyChange
     * @return int
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfDifficultyIncrement
     */
    private function sanitizeDifficulty($difficultyChange): int
    {
        try {
            return ToInteger::toPositiveInteger($difficultyChange);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfDifficultyIncrement(
                'Expected positive integer for difficulty increment , got ' . ValueDescriber::describe($difficultyChange)
            );
        }
    }

    /**
     * @param $addition
     * @return int
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     */
    private function sanitizeAddition($addition): int
    {
        try {
            return ToInteger::toInteger($addition);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatOfAdditionByDifficultyValue(
                'Expected integer for addition by difficulty, got ' . ValueDescriber::describe($addition)
            );
        }
    }

    /**
     * How is difficulty increased on addition step, @see getAdditionStep.
     *
     * @return int
     */
    public function getDifficultyOfAdditionStep(): int
    {
        return $this->difficultyOfAdditionStep;
    }

    /**
     * Bonus given by increasing difficulty by a point(s), @see getDifficultyOfAdditionStep
     *
     * @return int
     */
    public function getAdditionStep(): int
    {
        return $this->additionStep;
    }

    /**
     * Current value of a bonus paid by difficulty points, steps x @see getAdditionStep
     *
     * @return int
     */
    public function getCurrentAddition(): int
    {
        return $this->currentAddition;
    }

    /**
     * How much is difficulty increased to get total bonus, steps x @see getCurrentAddition
     *
     * @return int
     */
    public function getCurrentDifficultyIncrement(): int
    {
        return ceil($this->getCurrentAddition() / $this->getAdditionStep() * $this->getDifficultyOfAdditionStep());
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
     * @return AdditionByDifficulty
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     */
    public function add($value): AdditionByDifficulty
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
     * @return AdditionByDifficulty
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByDifficultyValue
     */
    public function sub($value): AdditionByDifficulty
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
        return "{$this->getValue()} {{$this->getDifficultyOfAdditionStep()}=>{$this->getAdditionStep()}}";
    }

    /**
     * @return string
     */
    public function getNotation(): string
    {
        return "{$this->getDifficultyOfAdditionStep()}={$this->getAdditionStep()}";
    }
}