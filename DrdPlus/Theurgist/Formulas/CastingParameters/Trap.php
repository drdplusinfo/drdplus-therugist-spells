<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Codes\Properties\PropertyCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\IntegerCastingParameter;
use Granam\Integer\Tools\ToInteger;
use Granam\Number\NumberInterface;
use Granam\Tools\ValueDescriber;

class Trap extends IntegerCastingParameter
{
    /**
     * @var PropertyCode
     */
    private $propertyCode;

    /**
     * @param array $values [0 => trap value, 1 => trap change by realms, 2=> used property, 3 => current addition]
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\InvalidValueForIntegerCastingParameter
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Partials\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfPropertyUsedForTrap
     */
    public function __construct(array $values)
    {
        $trapProperty = [];
        if (array_key_exists(2, $values)) { // it SHOULD exists
            $trapProperty[] = $values[2];
            unset($values[2]);
            $values = array_values($values); // reindexing
        }
        parent::__construct($values);
        try {
            $this->propertyCode = PropertyCode::getIt($trapProperty[0] ?? 0);
        } catch (\DrdPlus\Codes\Partials\Exceptions\UnknownValueForCode $unknownValueForCode) {
            throw new Exceptions\InvalidFormatOfPropertyUsedForTrap(
                'Expected valid property code, got '
                . (array_key_exists(0, $trapProperty) ? ValueDescriber::describe($trapProperty[0]) : 'nothing')
            );
        }
    }

    /**
     * @return PropertyCode
     */
    public function getPropertyCode(): PropertyCode
    {
        return $this->propertyCode;
    }

    /**
     * @param int|float|NumberInterface $additionValue
     * @return Trap
     * @throws \Granam\Integer\Tools\Exceptions\Exception
     */
    public function setAddition($additionValue): Trap
    {
        $additionValue = ToInteger::toInteger($additionValue);
        if ($additionValue === 0) {
            return $this;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            [
                $this->getValue(),
                $this->getAdditionByRealms()->getNotation(),
                $this->getPropertyCode(),
                $additionValue,
            ]
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getValue()} {$this->getPropertyCode()} ({$this->getAdditionByRealms()})";
    }
}