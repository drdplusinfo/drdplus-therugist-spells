<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use Granam\Integer\PositiveInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class FormulaDifficulty extends StrictObject implements PositiveInteger
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
     * @var FormulaDifficultyAddition
     */
    private $formulaDifficultyAddition;

    /**
     * @param array $values [ 0 => minimal, 1 => maximal, 2 => difficulty addition per realm, 3 => current difficulty addition value]
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidValueForMinimalDifficulty
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidValueForMaximalDifficulty
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Partials\Exceptions\MissingValueForFormulaDifficultyAddition
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsNotation
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfRealmsIncrement
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmsValue
     */
    public function __construct(array $values)
    {
        try {
            $this->minimal = ToInteger::toPositiveInteger($values[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForMinimalDifficulty(
                'Expected positive integer for minimal difficulty, got '
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0]) : 'nothing')
            );
        }
        try {
            $this->maximal = ToInteger::toPositiveInteger($values[1] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForMaximalDifficulty(
                'Expected positive integer for maximal difficulty, got '
                . (array_key_exists(1, $values) ? ValueDescriber::describe($values[1]) : 'nothing')
            );
        }
        if ($this->minimal > $this->maximal) {
            throw new Exceptions\MinimalDifficultyCanNotBeGreaterThanMaximal(
                'Minimal difficulty has to be lesser or equal to maximal.'
                . " Got minimum {$this->minimal} and maximum {$this->maximal}"
            );
        }
        if (!array_key_exists(2, $values)) {
            throw new Partials\Exceptions\MissingValueForFormulaDifficultyAddition(
                'Missing index 2 for formula difficulty addition by realm in given values ' . var_export($values, true)
                . ' for difficulty'
            );
        }
        $this->formulaDifficultyAddition = new FormulaDifficultyAddition($values[2], $values[3] ?? null /* current addition value */);
    }

    /**
     * Works as difficulty (some kind of "price") for basic, not changed formula.
     * Can differs from 'value', @see getValue, which is current difficulty of even modified formula.
     *
     * @return int
     */
    public function getMinimal(): int
    {
        return $this->minimal;
    }

    /**
     * Maximal difficulty a formula from lowest possible realm can handle.
     * Can be even LESS than 'value', @see getValue, which is current difficulty of even heavily modified formula.
     *
     * @return int
     */
    public function getMaximal(): int
    {
        return $this->maximal;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->getMinimal() + $this->formulaDifficultyAddition->getCurrentAddition();
    }

    /**
     * @return FormulaDifficultyAddition
     */
    public function getFormulaDifficultyAddition(): FormulaDifficultyAddition
    {
        return $this->formulaDifficultyAddition;
    }

    /**
     * @param int|float|NumberInterface $difficultyChangeValue
     * @return FormulaDifficulty
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function createWithChange($difficultyChangeValue): FormulaDifficulty
    {
        $difficultyChangeValue = ToInteger::toInteger($difficultyChangeValue);
        if ($difficultyChangeValue === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [
                $this->getMinimal(),
                $this->getMaximal(),
                $this->getFormulaDifficultyAddition()->getNotation(),
                $difficultyChangeValue,
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
        $asString .= ' [' . $this->getFormulaDifficultyAddition() . ']';
        $asString .= ')';

        return $asString;
    }
}