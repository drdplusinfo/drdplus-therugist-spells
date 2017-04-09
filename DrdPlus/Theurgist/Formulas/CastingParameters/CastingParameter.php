<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\GetParameterNameTrait;
use Granam\Strict\Object\StrictObject;

/** @noinspection SingletonFactoryPatternViolationInspection */
abstract class CastingParameter extends StrictObject
{
    use GetParameterNameTrait;

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
            throw new Exceptions\MissingValueForAdditionByRealm(
                "Missing index {$additionByRealmIndex} for addition by realm in given values " . var_export($values, true)
                . ' for ' . $this->getParameterName()
            );
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