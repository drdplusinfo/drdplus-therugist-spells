<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Tools\ValueDescriber;

abstract class IntegerCastingParameter extends CastingParameter implements IntegerInterface
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForIntegerCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    public function __construct(array $values)
    {
        try {
            $this->value = ToInteger::toInteger($values[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForIntegerCastingParameter(
                "Expected integer for {$this->getParameterName()}, got "
                . (array_key_exists(0, $values) ? ValueDescriber::describe($values[0], true) : 'nothing')
            );
        }
        parent::__construct($values, 1);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getValue()} ({$this->getAdditionByRealms()})";
    }

    /**
     * @param int|float|NumberInterface $value
     * @return IntegerCastingParameter
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function add($value)
    {
        $value = ToInteger::toInteger($value);
        if ($value === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [$this->getValue() + $value, $this->getAdditionByRealms()->getNotation()]
        );
    }

    /**
     * @param int|float|NumberInterface $value
     * @return IntegerCastingParameter
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function sub($value)
    {
        $value = ToInteger::toInteger($value);
        if ($value === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [$this->getValue() - $value, $this->getAdditionByRealms()->getNotation()]
        );
    }
}