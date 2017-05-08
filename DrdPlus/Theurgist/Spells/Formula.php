<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\FormulaMutableCastingParameterCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Attack;
use DrdPlus\Theurgist\Spells\CastingParameters\Brightness;
use DrdPlus\Theurgist\Spells\CastingParameters\DetailLevel;
use DrdPlus\Theurgist\Spells\CastingParameters\Duration;
use DrdPlus\Theurgist\Spells\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\CastingParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\CastingParameters\Power;
use DrdPlus\Theurgist\Spells\CastingParameters\Radius;
use DrdPlus\Theurgist\Spells\CastingParameters\Realm;
use DrdPlus\Theurgist\Spells\CastingParameters\SizeChange;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use Granam\Integer\IntegerObject;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Granam\Tools\ValueDescriber;

class Formula extends StrictObject
{
    use ToFlatArrayTrait;

    /** @var FormulaCode */
    private $formulaCode;
    /** @var FormulasTable */
    private $formulasTable;
    /** @var int[] */
    private $additions;
    /** @var Modifier[] */
    private $modifiers;

    /**
     * @param FormulaCode $formulaCode
     * @param FormulasTable $formulasTable
     * @param array $formulaAdditions by FormulaMutableCastingParameterCode value indexed its value change
     * @param Modifier[] $modifiers
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameterAddition
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidModifier
     */
    public function __construct(
        FormulaCode $formulaCode,
        FormulasTable $formulasTable,
        array $formulaAdditions,
        array $modifiers
    )
    {
        $this->formulaCode = $formulaCode;
        $this->formulasTable = $formulasTable;
        $this->additions = $this->sanitizeAdditions($formulaAdditions);
        $this->modifiers = $this->getCheckedModifiers($this->toFlatArray($modifiers));
    }

    /**
     * @param array $additions
     * @return array
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameterAddition
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     */
    private function sanitizeAdditions(array $additions): array
    {
        $sanitized = [];
        foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableCastingParameter) {
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
                            "Casting parameter {$mutableCastingParameter} is not used for formula {$this->formulaCode}"
                            . ', so given non-zero addition ' . ValueDescriber::describe($additions[$mutableCastingParameter])
                            . ' is thrown away'
                        );
                    }
                }
                $sanitized[$mutableCastingParameter] = $sanitizedValue;
            } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
                throw new Exceptions\InvalidValueForFormulaParameterAddition(
                    'Expected integer, got ' . ValueDescriber::describe($additions[$mutableCastingParameter])
                    . ' for ' . $mutableCastingParameter . ": '{$exception->getMessage()}'"
                );
            }
            unset($additions[$mutableCastingParameter]);
        }
        if (count($additions) > 0) { // there are some remains
            throw new Exceptions\UnknownFormulaParameter(
                'Unexpected mutable casting parameter(s) [' . implode(', ', array_keys($additions)) . ']. Expected only '
                . implode(', ', FormulaMutableCastingParameterCode::getPossibleValues())
            );
        }

        return $sanitized;
    }

    /**
     * @param array $modifiers
     * @return array|Modifier[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidModifier
     */
    private function getCheckedModifiers(array $modifiers): array
    {
        foreach ($modifiers as $modifier) {
            if (!is_a($modifier, Modifier::class)) {
                throw new Exceptions\InvalidModifier(
                    'Expected instance of ' . Modifier::class . ', got ' . ValueDescriber::describe($modifier)
                );
            }
        }

        return $modifiers;
    }

    public function getDifficultyOfChanged(): FormulaDifficulty
    {
        $parameters = [
            $this->getAttackWithAddition(),
            $this->getBrightnessWithAddition(),
            $this->getDetailLevelWithAddition(),
            $this->getDurationWithAddition(),
            $this->getEpicenterShiftWithAddition(),
            $this->getPowerWithAddition(),
            $this->getRadiusWithAddition(),
            $this->getSizeChangeWithAddition(),
            $this->getSpellSpeedWithAddition(),
        ];
        $parameters = array_filter($parameters, function (IntegerCastingParameter $castingParameter = null) {
            return $castingParameter !== null;
        });
        $parametersDifficultyChangeSum = 0;
        /** @var IntegerCastingParameter $parameter */
        foreach ($parameters as $parameter) {
            $parametersDifficultyChangeSum += $parameter->getAdditionByDifficulty()->getValue();
        }
        $modifiersDifficultyChangeSum = 0;
        foreach ($this->modifiers as $modifier) {
            $modifiersDifficultyChangeSum += $modifier->getDifficultyChange()->getValue();
        }
        $formulaDifficulty = $this->formulasTable->getFormulaDifficulty($this->getFormulaCode());

        return $formulaDifficulty->createWithChange($parametersDifficultyChangeSum + $modifiersDifficultyChangeSum);
    }

    public function getRequiredRealm(): Realm
    {
        $realmsIncrement = $this->getDifficultyOfChanged()->getFormulaDifficultyAddition()->getCurrentRealmsIncrement();
        $realm = $this->formulasTable->getRealm($this->getFormulaCode());

        return $realm->add($realmsIncrement);
    }

    /**
     * @return FormulaCode
     */
    public function getFormulaCode(): FormulaCode
    {
        return $this->formulaCode;
    }

    /**
     * @return Radius|null
     */
    public function getBaseRadius()
    {
        return $this->formulasTable->getRadius($this->formulaCode);
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
        return $this->additions[FormulaMutableCastingParameterCode::RADIUS];
    }

    /**
     * Radius can be only increased, not added
     *
     * @return Radius|null
     */
    public function getCurrentRadius()
    {
        $radiusWithAddition = $this->getRadiusWithAddition();
        if (!$radiusWithAddition) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius([
            $radiusWithAddition->getValue()
            + $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::RADIUS),
            0
        ]);
    }

    /**
     * @return EpicenterShift|null
     */
    public function getBaseEpicenterShift()
    {
        return $this->formulasTable->getEpicenterShift($this->formulaCode);
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
        return $this->additions[FormulaMutableCastingParameterCode::EPICENTER_SHIFT];
    }

    /**
     * Any formula (spell) can be shifted
     *
     * @return EpicenterShift|null
     */
    public function getCurrentEpicenterShift()
    {
        $epicenterShiftWithAddition = $this->getEpicenterShiftWithAddition();
        $epicenterShiftBonus = $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::EPICENTER_SHIFT);
        if (!$epicenterShiftWithAddition && $epicenterShiftBonus === 0) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift([
            ($epicenterShiftWithAddition
                ? $epicenterShiftWithAddition->getValue()
                : 0) // epicenter can be always shifted, even if formula itself is not
            + $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::EPICENTER_SHIFT),
            0
        ]);
    }

    /**
     * @return Power|null
     */
    public function getBasePower()
    {
        return $this->formulasTable->getPower($this->formulaCode);
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
        return $this->additions[FormulaMutableCastingParameterCode::POWER];
    }

    /**
     * Any formula (spell) can get a power, even if was passive and not harming before
     *
     * @return IntegerObject|null
     */
    public function getCurrentPower()
    {
        $powerWithAddition = $this->getPowerWithAddition();
        $powerBonus = $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::POWER);
        if (!$powerWithAddition && $powerBonus === 0) {
            return null;
        }

        return new IntegerObject(
            ($powerWithAddition
                ? $powerWithAddition->getValue()
                : 0)
            + $powerBonus
        );
    }

    /**
     * @return Attack|null
     */
    public function getBaseAttack()
    {
        return $this->formulasTable->getAttack($this->formulaCode);
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
        return $this->additions[FormulaMutableCastingParameterCode::ATTACK];
    }

    /**
     * Attack can be only increased, not added.
     *
     * @return IntegerObject|null
     */
    public function getCurrentAttack()
    {
        $attackWithAddition = $this->getAttackWithAddition();
        if (!$attackWithAddition) {
            return null;
        }

        return new IntegerObject(
            $attackWithAddition->getValue()
            + $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::ATTACK)
        );
    }

    private function getParameterBonusFromModifiers(string $parameterName): int
    {
        $bonus = 0;
        foreach ($this->modifiers as $modifier) {
            if ($modifier->getModifierCode()->getValue() === ModifierCode::GATE) {
                continue; // gate does not give bonus to a parameter, it is standalone being with its own parameters
            }
            if ($parameterName === ModifierMutableCastingParameterCode::POWER
                && $modifier->getModifierCode()->getValue() === ModifierCode::THUNDER
            ) {
                continue; // thunder power means a noise, does not affects formula power
            }
            $getParameterWithAddition = 'get' . ucfirst($parameterName) . 'WithAddition';
            /** like @see Modifier::getAttackWithAddition() */
            $modifierParameter = $modifier->$getParameterWithAddition();
            if ($modifierParameter === null) {
                continue;
            }
            /** @var IntegerCastingParameter $modifierParameter */
            $bonus += $modifierParameter->getValue();
        }

        return $bonus;
    }

    /**
     * @return SpellSpeed|null
     */
    public function getBaseSpellSpeed()
    {
        return $this->formulasTable->getSpellSpeed($this->formulaCode);
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
        return $this->additions[FormulaMutableCastingParameterCode::SPELL_SPEED];
    }

    /**
     * Any formula (spell) can get a speed, even if was static before
     *
     * @return SpellSpeed|null
     */
    public function getCurrentSpellSpeed()
    {
        $spellSpeedWithAddition = $this->getSpellSpeedWithAddition();
        $spellSpeedBonus = $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::SPELL_SPEED);
        if (!$spellSpeedWithAddition && $spellSpeedBonus === 0) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpellSpeed([
            ($spellSpeedWithAddition
                ? $spellSpeedWithAddition->getValue()
                : 0)
            + $this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::SPELL_SPEED),
            0
        ]);
    }

    /**
     * @return DetailLevel|null
     */
    public function getBaseDetailLevel()
    {
        return $this->formulasTable->getDetailLevel($this->formulaCode);
    }

    /**
     * @return DetailLevel|null
     */
    public function getDetailLevelWithAddition()
    {
        $baseDetailLevel = $this->getBaseDetailLevel();
        if ($baseDetailLevel === null) {
            return null;
        }

        return $baseDetailLevel->getWithAddition($this->getDetailLevelAddition());
    }

    public function getDetailLevelAddition(): int
    {
        return $this->additions[FormulaMutableCastingParameterCode::DETAIL_LEVEL];
    }

    /**
     * @return DetailLevel|null
     */
    public function getCurrentDetailLevel()
    {
        return $this->getDetailLevelWithAddition();
    }

    /**
     * @return Brightness|null
     */
    public function getBaseBrightness()
    {
        return $this->formulasTable->getBrightness($this->formulaCode);
    }

    /**
     * @return Brightness|null
     */
    public function getBrightnessWithAddition()
    {
        $baseBrightness = $this->getBaseBrightness();
        if ($baseBrightness === null) {
            return null;
        }

        return $baseBrightness->getWithAddition($this->getBrightnessAddition());
    }

    public function getBrightnessAddition(): int
    {
        return $this->additions[FormulaMutableCastingParameterCode::BRIGHTNESS];
    }

    /**
     * @return Brightness|null
     */
    public function getCurrentBrightness()
    {
        return $this->getBrightnessWithAddition();
    }

    /**
     * @return Duration
     */
    public function getBaseDuration(): Duration
    {
        return $this->formulasTable->getDuration($this->formulaCode);
    }

    /**
     * @return Duration
     */
    public function getDurationWithAddition(): Duration
    {
        $baseDuration = $this->getBaseDuration();

        return $baseDuration->getWithAddition($this->getDurationAddition());
    }

    public function getDurationAddition(): int
    {
        return $this->additions[FormulaMutableCastingParameterCode::DURATION];
    }

    public function getCurrentDuration(): Duration
    {
        return $this->getDurationWithAddition();
    }

    /**
     * @return SizeChange|null
     */
    public function getBaseSizeChange()
    {
        return $this->formulasTable->getSizeChange($this->formulaCode);
    }

    /**
     * @return SizeChange|null
     */
    public function getSizeChangeWithAddition()
    {
        $baseSizeChange = $this->getBaseSizeChange();
        if ($baseSizeChange === null) {
            return null;
        }

        return $baseSizeChange->getWithAddition($this->getSizeChangeAddition());
    }

    public function getSizeChangeAddition(): int
    {
        return $this->additions[FormulaMutableCastingParameterCode::SIZE_CHANGE];
    }

    /**
     * @return SizeChange|null
     */
    public function getCurrentSizeChange()
    {
        return $this->getSizeChangeWithAddition();
    }
}