<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters\Partials;

use DrdPlus\Theurgist\Formulas\CastingParameters\AdditionByRealms;
use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

abstract class IntegerCastingParameter extends StrictObject implements IntegerInterface
{
    use GetParameterNameTrait;

    /**
     * @var int
     */
    private $value;
    /**
     * @var AdditionByRealms
     */
    private $additionByRealms;

    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\InvalidValueForIntegerCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
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
        if (!array_key_exists(1, $values)) {
            throw new Exceptions\MissingValueForAdditionByRealm(
                'Missing index 1 for addition by realm in given values ' . var_export($values, true)
                . ' for ' . $this->getParameterName()
            );
        }
        $this->additionByRealms = new AdditionByRealms($values[1]);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return AdditionByRealms
     */
    public function getAdditionByRealms(): AdditionByRealms
    {
        return $this->additionByRealms;
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