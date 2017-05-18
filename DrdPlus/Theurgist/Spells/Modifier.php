<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableSpellParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Attack;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\Conditions;
use DrdPlus\Theurgist\Spells\SpellParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\SpellParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\SpellParameters\Grafts;
use DrdPlus\Theurgist\Spells\SpellParameters\Invisibility;
use DrdPlus\Theurgist\Spells\SpellParameters\NumberOfSituations;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\SpellParameters\Points;
use DrdPlus\Theurgist\Spells\SpellParameters\Power;
use DrdPlus\Theurgist\Spells\SpellParameters\Quality;
use DrdPlus\Theurgist\Spells\SpellParameters\Radius;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\RealmsAffection;
use DrdPlus\Theurgist\Spells\SpellParameters\Resistance;
use DrdPlus\Theurgist\Spells\SpellParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\SpellParameters\Threshold;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Granam\Tools\ValueDescriber;

class Modifier extends StrictObject
{
    use ToFlatArrayTrait;

    /** @var ModifierCode */
    private $modifierCode;
    /** @var ModifiersTable */
    private $modifiersTable;
    /** @var array|int[] */
    private $modifierSpellParameterChanges;
    /** @var array|SpellTrait[] */
    private $modifierSpellTraits;

    /**
     * @param ModifierCode $modifierCode
     * @param ModifiersTable $modifiersTable
     * @param array|int[] $modifierSpellParameterValues spell parameters current values (delta will be calculated from them)
     * by @see ModifierMutableSpellParameterCode value indexed its value change
     * @param array|SpellTrait[] $modifierSpellTraits
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessValueForUnusedSpellParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     */
    public function __construct(
        ModifierCode $modifierCode,
        ModifiersTable $modifiersTable,
        array $modifierSpellParameterValues,
        array $modifierSpellTraits
    )
    {
        $this->modifierCode = $modifierCode;
        $this->modifiersTable = $modifiersTable;
        $this->modifierSpellParameterChanges = $this->sanitizeSpellParameterChanges($modifierSpellParameterValues);
        $this->modifierSpellTraits = $this->getCheckedSpellTraits($this->toFlatArray($modifierSpellTraits));
    }

    /**
     * @param array $spellParameterValues
     * @return array
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessValueForUnusedSpellParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     */
    private function sanitizeSpellParameterChanges(array $spellParameterValues): array
    {
        $sanitized = [];
        foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableSpellParameter) {
            if (!array_key_exists($mutableSpellParameter, $spellParameterValues)) {
                $sanitized[$mutableSpellParameter] = 0; // no change
                continue;
            }
            try {
                $sanitizedValue = ToInteger::toInteger($spellParameterValues[$mutableSpellParameter]);
            } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
                throw new Exceptions\InvalidValueForModifierParameter(
                    'Expected integer, got ' . ValueDescriber::describe($spellParameterValues[$mutableSpellParameter])
                    . ' for ' . $mutableSpellParameter . ": '{$exception->getMessage()}'"
                );
            }
            /** like @see getBaseAttack */
            $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableSpellParameter);
            /** @var IntegerCastingParameter $baseParameter */
            $baseParameter = $this->$getBaseParameter();
            if ($baseParameter === null) {
                throw new Exceptions\UselessValueForUnusedSpellParameter(
                    "Casting parameter {$mutableSpellParameter} is not used for modifier {$this->modifierCode}"
                    . ', so given spell parameter value ' . ValueDescriber::describe($spellParameterValues[$mutableSpellParameter])
                    . ' is thrown away'
                );
            }
            $parameterChange = $sanitizedValue - $baseParameter->getDefaultValue();
            $sanitizedChanges[$mutableSpellParameter] = $parameterChange;
            $sanitized[$mutableSpellParameter] = $sanitizedValue;

            unset($spellParameterValues[$mutableSpellParameter]);
        }
        if (count($spellParameterValues) > 0) { // there are some remains
            throw new Exceptions\UnknownModifierParameter(
                'Unexpected mutable spell parameter(s) [' . implode(', ', array_keys($spellParameterValues)) . ']. Expected only '
                . implode(', ', ModifierMutableSpellParameterCode::getPossibleValues())
            );
        }

        return $sanitized;
    }

    /**
     * @param array $spellTraits
     * @return array|SpellTrait[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     */
    private function getCheckedSpellTraits(array $spellTraits): array
    {
        foreach ($spellTraits as $spellTrait) {
            if (!is_a($spellTrait, SpellTrait::class)) {
                throw new Exceptions\InvalidSpellTrait(
                    'Expected instance of ' . Modifier::class . ', got ' . ValueDescriber::describe($spellTrait)
                );
            }
        }

        return $spellTraits;
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
        $modifierParameters = [
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
        $modifierParameters = array_filter(
            $modifierParameters,
            function (IntegerCastingParameter $modifierParameter = null) {
                return $modifierParameter !== null;
            }
        );
        $parametersDifficultyChangeSum = 0;
        /** @var IntegerCastingParameter $parameter */
        foreach ($modifierParameters as $parameter) {
            $parametersDifficultyChangeSum += $parameter->getAdditionByDifficulty()->getCurrentDifficultyIncrement();
        }
        $spellTraitsDifficultyChangeSum = 0;
        foreach ($this->modifierSpellTraits as $spellTrait) {
            $spellTraitsDifficultyChangeSum += $spellTrait->getDifficultyChange()->getValue();
        }
        $difficultyChange = $this->modifiersTable->getDifficultyChange($this->getModifierCode());

        return $difficultyChange->add($parametersDifficultyChangeSum + $spellTraitsDifficultyChangeSum);
    }

    /**
     * @return CastingRounds
     */
    public function getCastingRounds(): CastingRounds
    {
        return $this->modifiersTable->getCastingRounds($this->getModifierCode());
    }

    /**
     * @return Realm
     */
    public function getRequiredRealm(): Realm
    {
        return $this->modifiersTable->getRealm($this->getModifierCode());
    }

    /**
     * @return RealmsAffection|null
     */
    public function getRealmsAffection()
    {
        return $this->modifiersTable->getRealmsAffection($this->getModifierCode());
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::RADIUS];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::EPICENTER_SHIFT];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::POWER];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::ATTACK];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::GRAFTS];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::SPELL_SPEED];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::INVISIBILITY];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::QUALITY];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::CONDITIONS];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::RESISTANCE];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::NUMBER_OF_SITUATIONS];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::THRESHOLD];
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
        return $this->modifierSpellParameterChanges[ModifierMutableSpellParameterCode::POINTS];
    }

    public function __toString()
    {
        return (string)$this->getModifierCode()->getValue();
    }
}