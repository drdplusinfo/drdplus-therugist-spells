<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\FormulaMutableCastingParameterCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Evocation;
use DrdPlus\Theurgist\Spells\SpellParameters\RealmsAffection;
use DrdPlus\Theurgist\Spells\SpellParameters\Attack;
use DrdPlus\Theurgist\Spells\SpellParameters\Brightness;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\DetailLevel;
use DrdPlus\Theurgist\Spells\SpellParameters\Duration;
use DrdPlus\Theurgist\Spells\SpellParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\SpellParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\SpellParameters\Power;
use DrdPlus\Theurgist\Spells\SpellParameters\Radius;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\SizeChange;
use DrdPlus\Theurgist\Spells\SpellParameters\SpellSpeed;
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
    private $formulaSpellParameterChanges;
    /** @var Modifier[] */
    private $modifiers;
    /** @var SpellTrait[] */
    private $formulaSpellTraits;

    /**
     * @param FormulaCode $formulaCode
     * @param FormulasTable $formulasTable
     * @param array $formulaSpellParameterChanges
     * by @see FormulaMutableCastingParameterCode value indexed its value change
     * @param array|Modifier[] $modifiers
     * @param array|SpellTrait[] $formulaSpellTraits
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidModifier
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     */
    public function __construct(
        FormulaCode $formulaCode,
        FormulasTable $formulasTable,
        array $formulaSpellParameterChanges,
        array $modifiers,
        array $formulaSpellTraits
    )
    {
        $this->formulaCode = $formulaCode;
        $this->formulasTable = $formulasTable;
        $this->formulaSpellParameterChanges = $this->sanitizeSpellParameterChanges($formulaSpellParameterChanges);
        $this->modifiers = $this->getCheckedModifiers($this->toFlatArray($modifiers));
        $this->formulaSpellTraits = $this->getCheckedSpellTraits($this->toFlatArray($formulaSpellTraits));
    }

    /**
     * @param array $additions
     * @return array
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     */
    private function sanitizeSpellParameterChanges(array $additions): array
    {
        $sanitized = [];
        foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableSpellParameter) {
            if (!array_key_exists($mutableSpellParameter, $additions)) {
                $sanitized[$mutableSpellParameter] = 0;
                continue;
            }
            try {
                $sanitizedValue = ToInteger::toInteger($additions[$mutableSpellParameter]);
            } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
                throw new Exceptions\InvalidValueForFormulaParameter(
                    'Expected integer, got ' . ValueDescriber::describe($additions[$mutableSpellParameter])
                    . ' for ' . $mutableSpellParameter . ": '{$exception->getMessage()}'"
                );
            }
            if ($sanitizedValue !== 0) {
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableSpellParameter);
                if ($this->$getBaseParameter() === null) {
                    throw new Exceptions\UselessAdditionForUnusedCastingParameter(
                        "Casting parameter {$mutableSpellParameter} is not used for formula {$this->formulaCode}"
                        . ', so given non-zero addition ' . ValueDescriber::describe($additions[$mutableSpellParameter])
                        . ' is thrown away'
                    );
                }
            }
            $sanitized[$mutableSpellParameter] = $sanitizedValue;

            unset($additions[$mutableSpellParameter]);
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
     * @return FormulaDifficulty
     */
    public function getBaseDifficulty(): FormulaDifficulty
    {
        return $this->formulasTable->getFormulaDifficulty($this->getFormulaCode());
    }

    public function getCurrentDifficulty(): FormulaDifficulty
    {
        $formulaParameters = [
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
        $formulaParameters = array_filter(
            $formulaParameters,
            function (IntegerCastingParameter $formulaParameter = null) {
                return $formulaParameter !== null;
            }
        );
        $parametersDifficultyChangeSum = 0;
        /** @var IntegerCastingParameter $formulaParameter */
        foreach ($formulaParameters as $formulaParameter) {
            $parametersDifficultyChangeSum += $formulaParameter->getAdditionByDifficulty()->getCurrentDifficultyIncrement();
        }
        $modifiersDifficultyChangeSum = 0;
        foreach ($this->modifiers as $modifier) {
            $modifiersDifficultyChangeSum += $modifier->getDifficultyChange()->getValue();
        }
        $spellTraitsDifficultyChangeSum = 0;
        foreach ($this->formulaSpellTraits as $spellTrait) {
            $spellTraitsDifficultyChangeSum += $spellTrait->getDifficultyChange()->getValue();
        }
        $formulaDifficulty = $this->formulasTable->getFormulaDifficulty($this->getFormulaCode());

        return $formulaDifficulty->createWithChange(
            $parametersDifficultyChangeSum
            + $modifiersDifficultyChangeSum
            + $spellTraitsDifficultyChangeSum
        );
    }

    public function getBaseCastingRounds(): CastingRounds
    {
        return $this->formulasTable->getCastingRounds($this->getFormulaCode());
    }

    /**
     * @return CastingRounds
     */
    public function getCurrentCastingRounds(): CastingRounds
    {
        $castingRoundsSum = 0;
        foreach ($this->modifiers as $modifier) {
            $castingRoundsSum += $modifier->getCastingRounds()->getValue();
        }
        $castingRoundsSum += $this->getBaseCastingRounds()->getValue();

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new CastingRounds([$castingRoundsSum]);
    }

    public function getCurrentEvocation(): Evocation
    {
        return $this->formulasTable->getEvocation($this->getFormulaCode());
    }

    public function getBaseRealmsAffection(): RealmsAffection
    {
        return $this->formulasTable->getRealmsAffection($this->getFormulaCode());
    }

    /**
     * Daily, monthly and lifetime affections of realms
     *
     * @return array|RealmsAffection[]
     */
    public function getCurrentRealmsAffections(): array
    {
        $realmsAffections = [];
        foreach ($this->getRealmsAffectionsSum() as $periodName => $periodSum) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $realmsAffections[$periodName] = new RealmsAffection([$periodSum, $periodName]);
        }

        return $realmsAffections;
    }

    /**
     * @return array|int[] by affection period indexed summary of that period realms-affection
     */
    private function getRealmsAffectionsSum(): array
    {
        $baseRealmsAffection = $this->getBaseRealmsAffection();
        $realmsAffectionsSum = [
            // like daily => -2
            $baseRealmsAffection->getAffectionPeriod()->getValue() => $baseRealmsAffection->getValue(),
        ];
        foreach ($this->modifiers as $modifier) {
            $modifierRealmsAffection = $modifier->getRealmsAffection();
            $modifierRealmsAffectionPeriod = $modifierRealmsAffection->getAffectionPeriod()->getValue();
            if (!array_key_exists($modifierRealmsAffectionPeriod, $realmsAffectionsSum)) {
                $realmsAffectionsSum[$modifierRealmsAffectionPeriod] = 0;
            }
            $realmsAffectionsSum[$modifierRealmsAffectionPeriod] += $modifierRealmsAffection->getValue();
        }

        return $realmsAffectionsSum;
    }

    public function getRequiredRealm(): Realm
    {
        $realmsIncrement = $this->getCurrentDifficulty()->getCurrentRealmsIncrement();
        $realm = $this->formulasTable->getRealm($this->getFormulaCode());
        $requiredRealm = $realm->add($realmsIncrement);

        foreach ($this->modifiers as $modifier) {
            $byModifierRequiredRealm = $modifier->getRequiredRealm();
            if ($requiredRealm->getValue() < $byModifierRequiredRealm->getValue()) {
                // some modifier requires even higher realm, so we are forced to increase it
                $requiredRealm = $byModifierRequiredRealm;
            }
        }

        return $requiredRealm;
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::RADIUS];
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
            + (int)$this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::RADIUS),
            0,
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::EPICENTER_SHIFT];
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
        if (!$epicenterShiftWithAddition && $epicenterShiftBonus === false) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift([
            ($epicenterShiftWithAddition
                ? $epicenterShiftWithAddition->getValue()
                : 0) // epicenter can be always shifted, even if formula itself is not
            + (int)$epicenterShiftBonus,
            0,
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::POWER];
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
        if (!$powerWithAddition && $powerBonus === false) {
            return null;
        }

        return new IntegerObject(
            ($powerWithAddition
                ? $powerWithAddition->getValue()
                : 0)
            + (int)$powerBonus
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::ATTACK];
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
            + (int)$this->getParameterBonusFromModifiers(ModifierMutableCastingParameterCode::ATTACK)
        );
    }

    /**
     * @param string $parameterName
     * @return bool|int
     */
    private function getParameterBonusFromModifiers(string $parameterName)
    {
        $bonusParts = [];
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
            $parameter = $modifier->$getParameterWithAddition();
            if ($parameter === null) {
                continue;
            }
            /** @var IntegerCastingParameter $parameter */
            $bonusParts[] = $parameter->getValue();
        }
        if (count($bonusParts) === 0) {
            return false;
        }

        return (int)array_sum($bonusParts);
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::SPELL_SPEED];
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
        if (!$spellSpeedWithAddition && $spellSpeedBonus === false) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpellSpeed([
            ($spellSpeedWithAddition
                ? $spellSpeedWithAddition->getValue()
                : 0)
            + (int)$spellSpeedBonus,
            0,
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::DETAIL_LEVEL];
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::BRIGHTNESS];
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::DURATION];
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
        return $this->formulaSpellParameterChanges[FormulaMutableCastingParameterCode::SIZE_CHANGE];
    }

    /**
     * @return SizeChange|null
     */
    public function getCurrentSizeChange()
    {
        return $this->getSizeChangeWithAddition();
    }

    public function __toString()
    {
        return (string)$this->getFormulaCode()->getValue();
    }
}