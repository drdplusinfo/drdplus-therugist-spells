<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Attack;
use DrdPlus\Theurgist\Spells\CastingParameters\Conditions;
use DrdPlus\Theurgist\Spells\CastingParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\CastingParameters\Grafts;
use DrdPlus\Theurgist\Spells\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Spells\CastingParameters\NumberOfSituations;
use DrdPlus\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\CastingParameters\Points;
use DrdPlus\Theurgist\Spells\CastingParameters\Power;
use DrdPlus\Theurgist\Spells\CastingParameters\Quality;
use DrdPlus\Theurgist\Spells\CastingParameters\Radius;
use DrdPlus\Theurgist\Spells\CastingParameters\Resistance;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\CastingParameters\Threshold;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Granam\Tools\ValueDescriber;

class Modifier extends StrictObject
{
    /** @var ModifierCode */
    private $modifierCode;
    /** @var ModifiersTable */
    private $modifiersTable;
    /** @var array|int[] */
    private $additions;

    /**
     * @param ModifierCode $modifierCode
     * @param ModifiersTable $modifiersTable
     * @param array $additions
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameterAddition
     */
    public function __construct(ModifierCode $modifierCode, ModifiersTable $modifiersTable, array $additions)
    {
        $this->modifierCode = $modifierCode;
        $this->modifiersTable = $modifiersTable;
        $this->additions = $this->sanitizeAdditions($additions);
    }

    /**
     * @param array $additions
     * @return array
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameterAddition
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     */
    private function sanitizeAdditions(array $additions): array
    {
        $sanitized = [];
        foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $mutableCastingParameter) {
            if (!array_key_exists($mutableCastingParameter, $additions)) {
                $sanitized[$mutableCastingParameter] = 0;
                continue;
            }
            try {
                $sanitizedValue = ToInteger::toInteger($additions[$mutableCastingParameter]);
                if ($sanitizedValue !== 0) {
                    $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableCastingParameter);
                    if ($this->$getBaseParameter() === null) {
                        throw new Exceptions\UselessAdditionForUnusedCastingParameter(
                            "Casting parameter {$mutableCastingParameter} is not used for modifier {$this->modifierCode}"
                            . ', so given non-zero addition ' . ValueDescriber::describe($additions[$mutableCastingParameter])
                            . ' is thrown away'
                        );
                    }
                }
                $sanitized[$mutableCastingParameter] = $sanitizedValue;
            } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
                throw new Exceptions\InvalidValueForModifierParameterAddition(
                    'Expected integer, got ' . ValueDescriber::describe($additions[$mutableCastingParameter])
                    . ' for ' . $mutableCastingParameter . ": '{$exception->getMessage()}'"
                );
            }
            unset($additions[$mutableCastingParameter]);
        }
        if (count($additions) > 0) { // there are some remains
            throw new Exceptions\UnknownModifierParameter(
                'Unexpected mutable casting parameter(s) [' . implode(', ', array_keys($additions)) . ']. Expected only '
                . implode(', ', ModifierMutableCastingParameterCode::getPossibleValues())
            );
        }

        return $sanitized;
    }

    /**
     * @return ModifierCode
     */
    public function getModifierCode(): ModifierCode
    {
        return $this->modifierCode;
    }

    public function getDifficultyChange(): DifficultyChange
    {
        $parameters = [
            $this->getAttackWithAddition(),
            $this->getConditionsWithAddition(),
            $this->getEpicenterShiftWithAddition(),
            $this->getGraftsWithAddition(),
            $this->getInvisibilityWithAddition(),
            $this->getNumberOfSituationsWithAddition(),
            $this->getPointsWithAddition(),
            $this->getPowerWithAddition(),
            $this->getQualityWithAddition(),
            $this->getRadiusWithAddition(),
            $this->getResistanceWithAddition(),
            $this->getSpellSpeedWithAddition(),
            $this->getThresholdWithAddition(),
        ];
        $parameters = array_filter($parameters, function (IntegerCastingParameter $castingParameter = null) {
            return $castingParameter !== null;
        });
        $parametersDifficultyChangeSum = 0;
        /** @var IntegerCastingParameter $parameter */
        foreach ($parameters as $parameter) {
            $parametersDifficultyChangeSum += $parameter->getAdditionByDifficulty()->getValue();
        }
        $difficultyChange = $this->modifiersTable->getDifficultyChange($this->getModifierCode());

        return $difficultyChange->add($parametersDifficultyChangeSum);
    }

    /**
     * @return Radius|null
     */
    public function getBaseRadius()
    {
        return $this->modifiersTable->getRadius($this->modifierCode);
    }

    /**
     * @return Radius|null
     */
    public function getRadiusWithAddition()
    {
        $baseRadius = $this->getBaseRadius();
        if ($baseRadius === null) {
            return null;
        }

        return $baseRadius->getWithAddition($this->getRadiusAddition());
    }

    public function getRadiusAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::RADIUS];
    }

    /**
     * @return EpicenterShift|null
     */
    public function getBaseEpicenterShift()
    {
        return $this->modifiersTable->getEpicenterShift($this->modifierCode);
    }

    /**
     * @return EpicenterShift|null
     */
    public function getEpicenterShiftWithAddition()
    {
        $baseEpicenterShift = $this->getBaseEpicenterShift();
        if ($baseEpicenterShift === null) {
            return null;
        }

        return $baseEpicenterShift->getWithAddition($this->getEpicenterShiftAddition());
    }

    public function getEpicenterShiftAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::EPICENTER_SHIFT];
    }

    /**
     * @return Power|null
     */
    public function getBasePower()
    {
        return $this->modifiersTable->getPower($this->modifierCode);
    }

    /**
     * @return Power|null
     */
    public function getPowerWithAddition()
    {
        $basePower = $this->getBasePower();
        if ($basePower === null) {
            return null;
        }

        return $basePower->getWithAddition($this->getPowerAddition());
    }

    public function getPowerAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::POWER];
    }

    /**
     * @return Attack|null
     */
    public function getBaseAttack()
    {
        return $this->modifiersTable->getAttack($this->modifierCode);
    }

    /**
     * @return Attack|null
     */
    public function getAttackWithAddition()
    {
        $baseAttack = $this->getBaseAttack();
        if ($baseAttack === null) {
            return null;
        }

        return $baseAttack->getWithAddition($this->getAttackAddition());
    }

    public function getAttackAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::ATTACK];
    }

    /**
     * @return Grafts|null
     */
    public function getBaseGrafts()
    {
        return $this->modifiersTable->getGrafts($this->modifierCode);
    }

    /**
     * @return Grafts|null
     */
    public function getGraftsWithAddition()
    {
        $baseGrafts = $this->getBaseGrafts();
        if ($baseGrafts === null) {
            return null;
        }

        return $baseGrafts->getWithAddition($this->getGraftsAddition());
    }

    public function getGraftsAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::GRAFTS];
    }

    /**
     * @return SpellSpeed|null
     */
    public function getBaseSpellSpeed()
    {
        return $this->modifiersTable->getSpellSpeed($this->modifierCode);
    }

    /**
     * @return SpellSpeed|null
     */
    public function getSpellSpeedWithAddition()
    {
        $baseSpellSpeed = $this->getBaseSpellSpeed();
        if ($baseSpellSpeed === null) {
            return null;
        }

        return $baseSpellSpeed->getWithAddition($this->getSpellSpeedAddition());
    }

    public function getSpellSpeedAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::SPELL_SPEED];
    }

    /**
     * @return Invisibility|null
     */
    public function getBaseInvisibility()
    {
        return $this->modifiersTable->getInvisibility($this->modifierCode);
    }

    /**
     * @return Invisibility|null
     */
    public function getInvisibilityWithAddition()
    {
        $baseInvisibility = $this->getBaseInvisibility();
        if ($baseInvisibility === null) {
            return null;
        }

        return $baseInvisibility->getWithAddition($this->getInvisibilityAddition());
    }

    public function getInvisibilityAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::INVISIBILITY];
    }

    /**
     * @return Quality|null
     */
    public function getBaseQuality()
    {
        return $this->modifiersTable->getQuality($this->modifierCode);
    }

    /**
     * @return Quality|null
     */
    public function getQualityWithAddition()
    {
        $baseQuality = $this->getBaseQuality();
        if ($baseQuality === null) {
            return null;
        }

        return $baseQuality->getWithAddition($this->getQualityAddition());
    }

    public function getQualityAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::QUALITY];
    }

    /**
     * @return Conditions|null
     */
    public function getBaseConditions()
    {
        return $this->modifiersTable->getConditions($this->modifierCode);
    }

    /**
     * @return Conditions|null
     */
    public function getConditionsWithAddition()
    {
        $baseConditions = $this->getBaseConditions();
        if ($baseConditions === null) {
            return null;
        }

        return $baseConditions->getWithAddition($this->getConditionsAddition());
    }

    public function getConditionsAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::CONDITIONS];
    }

    /**
     * @return Resistance|null
     */
    public function getBaseResistance()
    {
        return $this->modifiersTable->getResistance($this->modifierCode);
    }

    /**
     * @return Resistance|null
     */
    public function getResistanceWithAddition()
    {
        $baseResistance = $this->getBaseResistance();
        if ($baseResistance === null) {
            return null;
        }

        return $baseResistance->getWithAddition($this->getResistanceAddition());
    }

    public function getResistanceAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::RESISTANCE];
    }

    /**
     * @return NumberOfSituations|null
     */
    public function getBaseNumberOfSituations()
    {
        return $this->modifiersTable->getNumberOfSituations($this->modifierCode);
    }

    /**
     * @return NumberOfSituations|null
     */
    public function getNumberOfSituationsWithAddition()
    {
        $baseNumberOfSituations = $this->getBaseNumberOfSituations();
        if ($baseNumberOfSituations === null) {
            return null;
        }

        return $baseNumberOfSituations->getWithAddition($this->getNumberOfSituationsAddition());
    }

    public function getNumberOfSituationsAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::NUMBER_OF_SITUATIONS];
    }

    /**
     * @return Threshold|null
     */
    public function getBaseThreshold()
    {
        return $this->modifiersTable->getThreshold($this->modifierCode);
    }

    /**
     * @return Threshold|null
     */
    public function getThresholdWithAddition()
    {
        $baseThreshold = $this->getBaseThreshold();
        if ($baseThreshold === null) {
            return null;
        }

        return $baseThreshold->getWithAddition($this->getThresholdAddition());
    }

    public function getThresholdAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::THRESHOLD];
    }

    /**
     * @return Points|null
     */
    public function getBasePoints()
    {
        return $this->modifiersTable->getPoints($this->modifierCode);
    }

    /**
     * @return Points|null
     */
    public function getPointsWithAddition()
    {
        $basePoints = $this->getBasePoints();
        if ($basePoints === null) {
            return null;
        }

        return $basePoints->getWithAddition($this->getPointsAddition());
    }

    public function getPointsAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::POINTS];
    }
}