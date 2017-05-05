<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Attack;
use DrdPlus\Theurgist\Spells\CastingParameters\Conditions;
use DrdPlus\Theurgist\Spells\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\CastingParameters\Grafts;
use DrdPlus\Theurgist\Spells\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Spells\CastingParameters\NumberOfSituations;
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
    public function getCurrentRadius()
    {
        $baseRadius = $this->getBaseRadius();
        if ($baseRadius === null) {
            return null;
        }

        return $baseRadius->setAddition($this->getRadiusAddition());
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
    public function getCurrentEpicenterShift()
    {
        $baseEpicenterShift = $this->getBaseEpicenterShift();
        if ($baseEpicenterShift === null) {
            return null;
        }

        return $baseEpicenterShift->setAddition($this->getEpicenterShiftAddition());
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
    public function getCurrentPower()
    {
        $basePower = $this->getBasePower();
        if ($basePower === null) {
            return null;
        }

        return $basePower->setAddition($this->getPowerAddition());
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
    public function getCurrentAttack()
    {
        $baseAttack = $this->getBaseAttack();
        if ($baseAttack === null) {
            return null;
        }

        return $baseAttack->setAddition($this->getAttackAddition());
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
    public function getCurrentGrafts()
    {
        $baseGrafts = $this->getBaseGrafts();
        if ($baseGrafts === null) {
            return null;
        }

        return $baseGrafts->setAddition($this->getGraftsAddition());
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
    public function getCurrentSpellSpeed()
    {
        $baseSpellSpeed = $this->getBaseSpellSpeed();
        if ($baseSpellSpeed === null) {
            return null;
        }

        return $baseSpellSpeed->setAddition($this->getSpellSpeedAddition());
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
    public function getCurrentInvisibility()
    {
        $baseInvisibility = $this->getBaseInvisibility();
        if ($baseInvisibility === null) {
            return null;
        }

        return $baseInvisibility->setAddition($this->getInvisibilityAddition());
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
    public function getCurrentQuality()
    {
        $baseQuality = $this->getBaseQuality();
        if ($baseQuality === null) {
            return null;
        }

        return $baseQuality->setAddition($this->getQualityAddition());
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
    public function getCurrentConditions()
    {
        $baseConditions = $this->getBaseConditions();
        if ($baseConditions === null) {
            return null;
        }

        return $baseConditions->setAddition($this->getConditionsAddition());
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
    public function getCurrentResistance()
    {
        $baseResistance = $this->getBaseResistance();
        if ($baseResistance === null) {
            return null;
        }

        return $baseResistance->setAddition($this->getResistanceAddition());
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
    public function getCurrentNumberOfSituations()
    {
        $baseNumberOfSituations = $this->getBaseNumberOfSituations();
        if ($baseNumberOfSituations === null) {
            return null;
        }

        return $baseNumberOfSituations->setAddition($this->getNumberOfSituationsAddition());
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
    public function getCurrentThreshold()
    {
        $baseThreshold = $this->getBaseThreshold();
        if ($baseThreshold === null) {
            return null;
        }

        return $baseThreshold->setAddition($this->getThresholdAddition());
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
    public function getCurrentPoints()
    {
        $basePoints = $this->getBasePoints();
        if ($basePoints === null) {
            return null;
        }

        return $basePoints->setAddition($this->getPointsAddition());
    }

    public function getPointsAddition(): int
    {
        return $this->additions[ModifierMutableCastingParameterCode::POINTS];
    }
}