<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Tools\ValueDescriber;

class Difficulty extends PositiveCastingParameter
{
    /**
     * @var int
     */
    private $minimal;
    /**
     * @var int
     */
    private $maximal;

    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMinimalDifficulty
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForMaximalDifficulty
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(array $values)
    {
        try {
            $this->minimal = ToInteger::toPositiveInteger($values[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForMinimalDifficulty(
                'Expected positive integer for minimal difficulty, got '
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0], true) : 'nothing')
            );
        }
        try {
            $this->maximal = ToInteger::toPositiveInteger($values[1] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForMaximalDifficulty(
                'Expected positive integer for maximal difficulty, got '
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[1], true) : 'nothing')
            );
        }
        if ($this->minimal > $this->maximal) {
            throw new Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal(
                'Minimal difficulty has to be lesser or equal to maximal.'
                . " Got minimum {$this->minimal} and maximum {$this->maximal}"
            );
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        parent::__construct([
            $values[3] ?? $this->minimal /* minimum is used as default difficulty */,
            $values[2] ?? null,
        ]);
    }

    /**
     * Works as difficulty (some kind of "price") for basic formula.
     *
     * @return int
     */
    public function getMinimal(): int
    {
        return $this->minimal;
    }

    /**
     * Maximal difficulty a formula from lowest possible realm can handle.
     *
     * @return int
     */
    public function getMaximal(): int
    {
        return $this->maximal;
    }

    /**
     * @param int|float|NumberInterface $value
     * @return Difficulty
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function add($value): Difficulty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [
                $this->getMinimal(),
                $this->getMaximal(),
                $this->getAdditionByRealms()->getNotation(),
                $this->getValue() + ToInteger::toInteger($value) // current difficulty is injected via fourth index
            ]
        );
    }

    /**
     * @param int|float|NumberInterface $value
     * @return Difficulty
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function sub($value): Difficulty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [
                $this->getMinimal(),
                $this->getMaximal(),
                $this->getAdditionByRealms()->getNotation(),
                $this->getValue() - ToInteger::toInteger($value) // current difficulty is injected via fourth index
            ]
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $asString = (string)$this->getValue();
        $asString .= ' (' . $this->getMinimal() . '...' . $this->getMaximal();
        $asString .= ' [' . $this->getAdditionByRealms() . ']';
        $asString .= ')';

        return $asString;
    }
}