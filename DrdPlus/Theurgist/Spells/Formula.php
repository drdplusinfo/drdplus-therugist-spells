<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Codes\DistanceUnitCode;
use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\FormulaMutableSpellParameterCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableSpellParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Attack;
use DrdPlus\Theurgist\Spells\SpellParameters\Brightness;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\DetailLevel;
use DrdPlus\Theurgist\Spells\SpellParameters\Duration;
use DrdPlus\Theurgist\Spells\SpellParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\SpellParameters\Evocation;
use DrdPlus\Theurgist\Spells\SpellParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\CastingParameter;
use DrdPlus\Theurgist\Spells\SpellParameters\Power;
use DrdPlus\Theurgist\Spells\SpellParameters\Radius;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\RealmsAffection;
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
    /** @var DistanceTable */
    private $distanceTable;
    /** @var int[] */
    private $formulaSpellParameterChanges;
    /** @var Modifier[] */
    private $modifiers;
    /** @var SpellTrait[] */
    private $formulaSpellTraits;

    /**
     * @param FormulaCode $formulaCode
     * @param FormulasTable $formulasTable
     * @param DistanceTable $distanceTable
     * @param array $formulaSpellParameterValues Current values of spell parameters (changes will be calculated from them)
     * by @see FormulaMutableSpellParameterCode value indexed its value change
     * @param array|Modifier[] $modifiers
     * @param array|SpellTrait[] $formulaSpellTraits
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessValueForUnusedSpellParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidModifier
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     */
    public function __construct(
        FormulaCode $formulaCode,
        FormulasTable $formulasTable,
        DistanceTable $distanceTable,
        array $formulaSpellParameterValues = [],
        array $modifiers = [],
        array $formulaSpellTraits = []
    )
    {
        $this->formulaCode = $formulaCode;
        $this->formulasTable = $formulasTable;
        $this->distanceTable = $distanceTable;
        // gets spell parameter changes as delta of current values and default values
        $this->formulaSpellParameterChanges = $this->sanitizeSpellParameterChanges($formulaSpellParameterValues);
        $this->modifiers = $this->getCheckedModifiers($this->toFlatArray($modifiers));
        $this->formulaSpellTraits = $this->getCheckedSpellTraits($this->toFlatArray($formulaSpellTraits));
    }

    /**
     * @param array $spellParameterValues
     * @return array
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UselessValueForUnusedSpellParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameter
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     */
    private function sanitizeSpellParameterChanges(array $spellParameterValues): array
    {
        $sanitizedChanges = [];
        foreach (FormulaMutableSpellParameterCode::getPossibleValues() as $mutableSpellParameter) {
            if (!array_key_exists($mutableSpellParameter, $spellParameterValues)) {
                $sanitizedChanges[$mutableSpellParameter] = 0;
                continue;
            }
            try {
                $sanitizedValue = ToInteger::toInteger($spellParameterValues[$mutableSpellParameter]);
            } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
                throw new Exceptions\InvalidValueForFormulaParameter(
                    'Expected integer, got ' . ValueDescriber::describe($spellParameterValues[$mutableSpellParameter])
                    . ' for ' . $mutableSpellParameter . ": '{$exception->getMessage()}'"
                );
            }
            /** like @see getBaseAttack */
            $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableSpellParameter);
            /** @var CastingParameter $baseParameter */
            $baseParameter = $this->$getBaseParameter();
            if ($baseParameter === null) {
                throw new Exceptions\UselessValueForUnusedSpellParameter(
                    "Casting parameter {$mutableSpellParameter} is not used for formula {$this->formulaCode}"
                    . ', so given non-zero addition ' . ValueDescriber::describe($spellParameterValues[$mutableSpellParameter])
                    . ' is thrown away'
                );
            }
            $parameterChange = $sanitizedValue - $baseParameter->getDefaultValue();
            $sanitizedChanges[$mutableSpellParameter] = $parameterChange;

            unset($spellParameterValues[$mutableSpellParameter]);
        }
        if (count($spellParameterValues) > 0) { // there are some remains
            throw new Exceptions\UnknownFormulaParameter(
                'Unexpected mutable spells parameter(s) [' . implode(', ', array_keys($spellParameterValues)) . ']. Expected only '
                . implode(', ', FormulaMutableSpellParameterCode::getPossibleValues())
            );
        }

        return $sanitizedChanges;
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
            function (CastingParameter $formulaParameter = null) {
                return $formulaParameter !== null;
            }
        );
        $parametersDifficultyChangeSum = 0;
        /** @var CastingParameter $formulaParameter */
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

    /**
     * Evocation time is not affected by any modifier or trait.
     *
     * @return Evocation
     */
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
            if ($modifierRealmsAffection === null) {
                continue;
            }
            $modifierRealmsAffectionPeriod = $modifierRealmsAffection->getAffectionPeriod()->getValue();
            if (!array_key_exists($modifierRealmsAffectionPeriod, $realmsAffectionsSum)) {
                $realmsAffectionsSum[$modifierRealmsAffectionPeriod] = 0;
            }
            $realmsAffectionsSum[$modifierRealmsAffectionPeriod] += $modifierRealmsAffection->getValue();
        }

        return $realmsAffectionsSum;
    }

    /**
     * Gives the highest required realm (by difficulty, by formula itself or by one of its modifiers)
     *
     * @return Realm
     */
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
     * Formula radius extended by direct formula change
     *
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::RADIUS];
    }

    /**
     * Final radius including direct formula change and all its active traits and modifiers.
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
            + (int)$this->getParameterBonusFromModifiers(ModifierMutableSpellParameterCode::RADIUS),
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::EPICENTER_SHIFT];
    }

    /**
     * Any formula (spell) can be shifted
     *
     * @return EpicenterShift|null
     */
    public function getCurrentEpicenterShift()
    {
        $epicenterShiftWithAddition = $this->getEpicenterShiftWithAddition();
        $epicenterShiftByModifiers = $this->getParameterBonusFromModifiers(ModifierMutableSpellParameterCode::EPICENTER_SHIFT);
        if ($epicenterShiftWithAddition === null) {
            if ($epicenterShiftByModifiers === false) {
                return null;
            }

            /** @noinspection ExceptionsAnnotatingAndHandlingInspection epicenter can be always shifted, even if formula itself is not */
            return new EpicenterShift([$epicenterShiftByModifiers, 0 /* no added difficulty*/]);
        }
        if ($epicenterShiftByModifiers === false) {
            return $epicenterShiftWithAddition;
        }
        $meters = $epicenterShiftWithAddition->getDistance($this->distanceTable)->getMeters();
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $meters += (new EpicenterShift([$epicenterShiftByModifiers, 0]))->getDistance($this->distanceTable)->getMeters();

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift([
            (new Distance($meters, DistanceUnitCode::METER, $this->distanceTable))->getBonus(),
            0 /* no added difficulty */
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::POWER];
    }

    /**
     * Any formula (spell) can get a power, even if was passive and not harming before
     *
     * @return IntegerObject|null
     */
    public function getCurrentPower()
    {
        $powerWithAddition = $this->getPowerWithAddition();
        $powerBonus = $this->getParameterBonusFromModifiers(ModifierMutableSpellParameterCode::POWER);
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::ATTACK];
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
            + (int)$this->getParameterBonusFromModifiers(ModifierMutableSpellParameterCode::ATTACK)
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
            if ($parameterName === ModifierMutableSpellParameterCode::POWER
                && $modifier->getModifierCode()->getValue() === ModifierCode::THUNDER
            ) {
                continue; // thunder power means a noise, does not affects formula power
            }
            $getParameterWithAddition = StringTools::assembleGetterForName($parameterName . 'WithAddition');
            /** like @see Modifier::getAttackWithAddition() */
            $parameter = $modifier->$getParameterWithAddition();
            if ($parameter === null) {
                continue;
            }
            /** @var CastingParameter $parameter */
            $bonusParts[] = $parameter->getValue();
        }
        if (count($bonusParts) === 0) {
            return false;
        }

        // transpositions are chained in sequence and their values (distances) have to be summed, not bonuses
        if (ModifierMutableSpellParameterCode::EPICENTER_SHIFT) {
            $meters = 0;
            foreach ($bonusParts as $bonusPart) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $meters += (new DistanceBonus($bonusPart, $this->distanceTable))->getDistance()->getMeters();
            }

            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return (new Distance($meters, DistanceUnitCode::METER, $this->distanceTable))->getBonus()->getValue();
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::SPELL_SPEED];
    }

    /**
     * Any formula (spell) can get a speed, even if was static before
     *
     * @return SpellSpeed|null
     */
    public function getCurrentSpellSpeed()
    {
        $spellSpeedWithAddition = $this->getSpellSpeedWithAddition();
        $spellSpeedBonus = $this->getParameterBonusFromModifiers(ModifierMutableSpellParameterCode::SPELL_SPEED);
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::DETAIL_LEVEL];
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::BRIGHTNESS];
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::DURATION];
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
        return $this->formulaSpellParameterChanges[FormulaMutableSpellParameterCode::SIZE_CHANGE];
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

    /**
     * @return Modifier|null
     */
    public function getColor()
    {
        return $this->getModifier(ModifierCode::COLOR);
    }

    /**
     * @param string $modifierName
     * @return Modifier|null
     */
    private function getModifier(string $modifierName)
    {
        foreach ($this->modifiers as $modifier) {
            if ($modifier->getModifierCode()->getValue() === $modifierName) {
                return $modifier;
            }
        }

        return null;
    }

    /**
     * @return Modifier|null
     */
    public function getGate()
    {
        return $this->getModifier(ModifierCode::GATE);
    }

    /**
     * @return Modifier|null
     */
    public function getExplosion()
    {
        return $this->getModifier(ModifierCode::EXPLOSION);
    }

    /**
     * @return Modifier|null
     */
    public function getFilter()
    {
        return $this->getModifier(ModifierCode::FILTER);
    }

    /**
     * @return Modifier|null
     */
    public function getInvisibility()
    {
        return $this->getModifier(ModifierCode::INVISIBILITY);
    }

    /**
     * @return Modifier|null
     */
    public function getMovement()
    {
        return $this->getModifier(ModifierCode::MOVEMENT);
    }

    /**
     * @return Modifier|null
     */
    public function getThunder()
    {
        return $this->getModifier(ModifierCode::THUNDER);
    }
}