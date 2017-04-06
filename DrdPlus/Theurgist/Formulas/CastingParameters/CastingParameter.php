<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use Granam\Strict\Object\StrictObject;

/** @noinspection SingletonFactoryPatternViolationInspection */
abstract class CastingParameter extends StrictObject
{
    /**
     * @var AdditionByRealm
     */
    private $additionByRealm;

    /**
     * @param array $values
     * @param int $index
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmsNumber
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAddition
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    protected function __construct(array $values, int $index)
    {
        if (!array_key_exists($index, $values)) {
            throw new Exceptions\MissingValueForAdditionByRealm('Missing value for addition by realm');
        }
        $this->additionByRealm = new AdditionByRealm($values[$index]);
    }

    /**
     * @return AdditionByRealm
     */
    public function getAdditionByRealm(): AdditionByRealm
    {
        return $this->additionByRealm;
    }
}