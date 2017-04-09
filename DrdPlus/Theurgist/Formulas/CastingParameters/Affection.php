<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Codes\AffectionTypeCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Partials\GetParameterNameTrait;
use Granam\Integer\NegativeInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class Affection extends StrictObject implements NegativeInteger
{
    use GetParameterNameTrait;

    /**
     * @var int
     */
    private $value;
    /**
     * @var AffectionTypeCode
     */
    private $affectionType;

    /**
     * @param array $affectionParts
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidFormatForNegativeCastingParameter
     */
    public function __construct(array $affectionParts)
    {
        try {
            $this->value = ToInteger::toNegativeInteger($affectionParts[0] ?? null);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidFormatForNegativeCastingParameter(
                'Expected negative integer, got '
                . (array_key_exists(0, $affectionParts)
                    ? ValueDescriber::describe($affectionParts[0])
                    : 'nothing'
                ) . ' for ' . $this->getParameterName()
            );
        }
        $this->affectionType = AffectionTypeCode::getIt($affectionParts[1] ?? AffectionTypeCode::DAILY);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return AffectionTypeCode
     */
    public function getAffectionType(): AffectionTypeCode
    {
        return $this->affectionType;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }

}