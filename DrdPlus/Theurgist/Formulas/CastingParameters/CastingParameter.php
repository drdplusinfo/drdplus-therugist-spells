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
     * @param int $additionByRealmIndex
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmsNumber
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAddition
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    protected function __construct(array $values, int $additionByRealmIndex)
    {
        if (!array_key_exists($additionByRealmIndex, $values)) {
            throw new Exceptions\MissingValueForAdditionByRealm('Missing value for addition by realm');
        }
        $this->additionByRealm = new AdditionByRealm($values[$additionByRealmIndex]);
    }

    /**
     * @return AdditionByRealm
     */
    public function getAdditionByRealm(): AdditionByRealm
    {
        return $this->additionByRealm;
    }
}