<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use Granam\Integer\IntegerInterface;
use Granam\Strict\Object\StrictObject;

class PropertyChange extends StrictObject implements IntegerInterface
{
    /**
     * @var int
     */
    private $propertyChangeValue;
    /**
     * @var int
     */
    private $difficultyChangeValue;

    /**
     * @param int $propertyChangeValue
     * @param int $difficultyChangeValue
     */
    public function __construct(int $propertyChangeValue, int $difficultyChangeValue)
    {
        $this->propertyChangeValue = $propertyChangeValue;
        $this->difficultyChangeValue = $difficultyChangeValue;
    }

    /**
     * @return int
     */
    public function getPropertyChangeValue(): int
    {
        return $this->propertyChangeValue;
    }

    /**
     * @return int
     */
    public function getDifficultyChangeValue(): int
    {
        return $this->difficultyChangeValue;
    }

    public function getValue(): int
    {
        return $this->getPropertyChangeValue();
    }

    public function __toString(): string
    {
        return "{$this->getDifficultyChangeValue()}=>{$this->getPropertyChangeValue()}";
    }

}