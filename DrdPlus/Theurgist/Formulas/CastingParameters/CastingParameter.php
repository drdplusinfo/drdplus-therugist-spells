<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\GetParameterNameTrait;
use Granam\Strict\Object\StrictObject;

/** @noinspection SingletonFactoryPatternViolationInspection */
abstract class CastingParameter extends StrictObject
{
    use GetParameterNameTrait;

    /**
     * @var AdditionByRealms
     */
    private $additionByRealms;

    /**
     * @param array $values
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfRealmIncrement
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatOfAdditionByRealmValue
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfAdditionByRealm
     */
    protected function __construct(array $values)
    {
        if (!array_key_exists(1, $values)) {
            throw new Exceptions\MissingValueForAdditionByRealm(
                'Missing index 1 for addition by realm in given values ' . var_export($values, true)
                . ' for ' . $this->getParameterName()
            );
        }
        $this->additionByRealms = new AdditionByRealms($values[1]);
    }

    /**
     * @return AdditionByRealms
     */
    public function getAdditionByRealms(): AdditionByRealms
    {
        return $this->additionByRealms;
    }
}