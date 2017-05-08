<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Affection;
use DrdPlus\Theurgist\Spells\CastingParameters\Attack;
use DrdPlus\Theurgist\Spells\CastingParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\CastingParameters\Conditions;
use DrdPlus\Theurgist\Spells\CastingParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\CastingParameters\Grafts;
use DrdPlus\Theurgist\Spells\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Spells\CastingParameters\Points;
use DrdPlus\Theurgist\Spells\CastingParameters\Power;
use DrdPlus\Theurgist\Spells\CastingParameters\PropertyChange;
use DrdPlus\Theurgist\Spells\CastingParameters\Quality;
use DrdPlus\Theurgist\Spells\CastingParameters\Radius;
use DrdPlus\Theurgist\Spells\CastingParameters\Realm;
use DrdPlus\Theurgist\Spells\CastingParameters\Resistance;
use DrdPlus\Theurgist\Spells\CastingParameters\NumberOfSituations;
use DrdPlus\Theurgist\Spells\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Spells\CastingParameters\Threshold;
use Granam\Integer\IntegerObject;

class ModifiersTable extends AbstractFileTable
{
    use ToFlatArrayTrait;

    /**
     * @var Tables
     */
    private $tables;

    /**
     * @param Tables $tables
     */
    public function __construct(Tables $tables)
    {
        $this->tables = $tables;
    }

    /**
     * @return string
     */
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/modifiers.csv';
    }

    const REALM = 'realm';
    const AFFECTION = 'affection';
    const AFFECTION_TYPE = 'affection_type';
    const CASTING_ROUNDS = 'casting_rounds';
    const DIFFICULTY_CHANGE = 'difficulty_change';
    const RADIUS = 'radius';
    const EPICENTER_SHIFT = 'epicenter_shift';
    const POWER = 'power';
    const ATTACK = 'attack';
    const GRAFTS = 'grafts';
    const SPELL_SPEED = 'spell_speed';
    const POINTS = 'points';
    const INVISIBILITY = 'invisibility';
    const QUALITY = 'quality';
    const CONDITIONS = 'conditions';
    const RESISTANCE = 'resistance';
    const NUMBER_OF_SITUATIONS = 'number_of_situations';
    const THRESHOLD = 'threshold';
    const FORMS = 'forms';
    const TRAITS = 'traits';
    const PROFILES = 'profiles';
    const FORMULAS = 'formulas';
    const PARENT_MODIFIERS = 'parent_modifiers';
    const CHILD_MODIFIERS = 'child_modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::AFFECTION => self::ARRAY,
            self::CASTING_ROUNDS => self::ARRAY,
            self::DIFFICULTY_CHANGE => self::POSITIVE_INTEGER,
            self::RADIUS => self::ARRAY,
            self::EPICENTER_SHIFT => self::ARRAY,
            self::POWER => self::ARRAY,
            self::ATTACK => self::ARRAY,
            self::GRAFTS => self::ARRAY,
            self::SPELL_SPEED => self::ARRAY,
            self::POINTS => self::ARRAY,
            self::INVISIBILITY => self::ARRAY,
            self::QUALITY => self::ARRAY,
            self::CONDITIONS => self::ARRAY,
            self::RESISTANCE => self::ARRAY,
            self::NUMBER_OF_SITUATIONS => self::ARRAY,
            self::THRESHOLD => self::ARRAY,
            self::FORMS => self::ARRAY,
            self::TRAITS => self::ARRAY,
            self::PROFILES => self::ARRAY,
            self::FORMULAS => self::ARRAY,
            self::PARENT_MODIFIERS => self::ARRAY,
            self::CHILD_MODIFIERS => self::ARRAY,
        ];
    }

    const MODIFIER = 'modifier';

    protected function getRowsHeader(): array
    {
        return [
            self::MODIFIER,
        ];
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Realm
     */
    public function getRealm(ModifierCode $modifierCode): Realm
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Realm($this->getValue($modifierCode, self::REALM));
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return Realm
     */
    public function getHighestRequiredRealm(array $modifierCodes): Realm
    {
        $modifierCodes = $this->toFlatArray($modifierCodes);
        if (count($modifierCodes) === 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return new Realm(0);
        }
        $realms = array_map(
            function ($modifierCodesOrCode) {
                return $this->getRealm($modifierCodesOrCode);
            },
            $this->toFlatArray($modifierCodes)
        );
        $highestRealm = current($realms);
        /** @var Realm $realm */
        foreach ($realms as $realm) {
            if ($realm->getValue() > $highestRealm->getValue()) {
                $highestRealm = $realm;
            }
        }

        return $highestRealm;
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Affection|null
     */
    public function getAffection(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $affectionValues = $this->getValue($modifierCode, self::AFFECTION);
        if (count($affectionValues) === 0) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Affection($affectionValues);
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return array|Affection[]
     */
    public function getAffectionsOfModifiers(array $modifierCodes): array
    {
        if (count($modifierCodes) === 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return [];
        }
        $affections = array_filter(
            array_map(
                function ($modifierCodesOrCode) {
                    return $this->getAffection($modifierCodesOrCode);
                },
                $this->toFlatArray($modifierCodes)
            ),
            function (Affection $affection = null) {
                return $affection !== null;
            }
        );

        $summedAffections = [];
        /** @var Affection $affection */
        foreach ($affections as $affection) {
            $affectionPeriodValue = $affection->getAffectionPeriod()->getValue();
            if (!array_key_exists($affectionPeriodValue, $summedAffections)) {
                $summedAffections[$affectionPeriodValue] = $affection;
                continue;
            }
            /** @var Affection $summedAffection */
            $summedAffection = $summedAffections[$affectionPeriodValue];
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $summedAffections[$affectionPeriodValue] = new Affection([
                $summedAffection->getValue() + $affection->getValue(),
                $affectionPeriodValue,
            ]);
        }

        return $summedAffections;
    }

    /**
     * @param ModifierCode $modifierCode
     * @return CastingRounds
     */
    public function getCastingRounds(ModifierCode $modifierCode): CastingRounds
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new CastingRounds($this->getValue($modifierCode, self::CASTING_ROUNDS));
    }

    /**
     * @param array $modifierCodes
     * @return CastingRounds
     */
    public function sumCastingRoundsChange(array $modifierCodes): CastingRounds
    {
        $castingSum = 0;
        foreach ($this->toFlatArray($modifierCodes) as $modifierCode) {
            $castingSum += $this->getCastingRounds($modifierCode)->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new CastingRounds([$castingSum, 0 /* no additions by realm */]);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return DifficultyChange
     */
    public function getDifficultyChange(ModifierCode $modifierCode): DifficultyChange
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DifficultyChange($this->getValue($modifierCode, self::DIFFICULTY_CHANGE));
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return DifficultyChange
     */
    public function sumDifficultyChanges(array $modifierCodes): DifficultyChange
    {
        return new DifficultyChange(
            array_sum(
                array_map(function ($modifierCodesOrCode) {
                    /** @var ModifierCode $modifierCodesOrCode */
                    return $this->getDifficultyChange($modifierCodesOrCode)->getValue();
                }, $this->toFlatArray($modifierCodes))
            )
        );
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Radius|null
     */
    public function getRadius(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $radiusValues = $this->getValue($modifierCode, self::RADIUS);
        if (!$radiusValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius($radiusValues, $this->tables->getDistanceTable());
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     */
    public function sumRadiusChange(array $modifierCodes): IntegerObject
    {
        $radiusValue = 0;
        foreach ($modifierCodes as $modifierCode) {
            $radius = $this->getRadius($modifierCode);
            if (!$radius) {
                continue;
            }
            $radiusValue += $radius->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new IntegerObject($radiusValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return EpicenterShift|null
     */
    public function getEpicenterShift(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shiftValues = $this->getValue($modifierCode, self::EPICENTER_SHIFT);
        if (!$shiftValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift($shiftValues, $this->tables->getDistanceTable());
    }

    /**
     * Transposition can shift epicenter.
     *
     * @param array|ModifierCode[] $modifierCodes
     * @return bool
     */
    public function isEpicenterShifted(array $modifierCodes): bool
    {
        foreach ($this->toFlatArray($modifierCodes) as $modifierCode) {
            $shift = $this->getEpicenterShift($modifierCode);
            if ($shift) {
                return true;
            }
        }

        return false;
    }

    /**
     * Transposition can shift epicenter.
     *
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     */
    public function sumEpicenterShiftChange(array $modifierCodes): IntegerObject
    {
        $shiftSum = 0;
        foreach ($this->toFlatArray($modifierCodes) as $modifierCode) {
            $shift = $this->getEpicenterShift($modifierCode);
            if (!$shift) {
                continue;
            }
            $shiftSum += $shift->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new IntegerObject($shiftSum);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Power|null
     */
    public function getPower(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $powerValues = $this->getValue($modifierCode, self::POWER);
        if (!$powerValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Power($powerValues);
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     */
    public function sumPowerChanges(array $modifierCodes): IntegerObject
    {
        $powerValue = 0;
        foreach ($modifierCodes as $modifierCode) {
            $power = $this->getPower($modifierCode);
            if (!$power) {
                continue;
            }
            $powerValue += $power->getValue();
        }

        return new IntegerObject($powerValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Attack|null
     */
    public function getAttack(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $attackValues = $this->getValue($modifierCode, self::ATTACK);
        if (!$attackValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Attack($attackValues);
    }

    /**
     * @param array|Modifier[] $modifiers
     * @return PropertyChange
     */
    public function sumAttackChange(array $modifiers): PropertyChange
    {
        $attackSum = 0;
        $difficultySum = 0;
        /** @var Modifier $modifier */
        foreach ($this->toFlatArray($modifiers) as $modifier) {
            $attack = $modifier->getAttackWithAddition();
            if (!$attack) {
                continue;
            }
            $attackSum += $attack->getValue();
            $difficultySum += $attack->getAdditionByDifficulty()->getCurrentDifficultyIncrement();
        }

        return new PropertyChange($attackSum, $difficultySum);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Grafts|null
     */
    public function getGrafts(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $graftsValues = $this->getValue($modifierCode, self::GRAFTS);
        if (!$graftsValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Grafts($graftsValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return SpellSpeed|null
     */
    public function getSpellSpeed(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $speedValues = $this->getValue($modifierCode, self::SPELL_SPEED);
        if (!$speedValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpellSpeed($speedValues, $this->tables->getSpeedTable());
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     */
    public function sumSpellSpeedChange(array $modifierCodes): IntegerObject
    {
        $speedSum = 0;
        foreach ($this->toFlatArray($modifierCodes) as $modifierCode) {
            $speed = $this->getSpellSpeed($modifierCode);
            if (!$speed) {
                continue;
            }
            $speedSum += $speed->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new IntegerObject($speedSum);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Points|null
     */
    public function getPoints(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $pointsValues = $this->getValue($modifierCode, self::POINTS);
        if (!$pointsValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Points($pointsValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Invisibility|null
     */
    public function getInvisibility(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $invisibilityValues = $this->getValue($modifierCode, self::INVISIBILITY);
        if (!$invisibilityValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Invisibility($invisibilityValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Quality|null
     */
    public function getQuality(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $qualityValues = $this->getValue($modifierCode, self::QUALITY);
        if (!$qualityValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Quality($qualityValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Conditions|null
     */
    public function getConditions(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $conditionsValues = $this->getValue($modifierCode, self::CONDITIONS);
        if (!$conditionsValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Conditions($conditionsValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Resistance|null
     */
    public function getResistance(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $resistanceValue = $this->getValue($modifierCode, self::RESISTANCE);
        if (!$resistanceValue) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Resistance($resistanceValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return NumberOfSituations|null
     */
    public function getNumberOfSituations(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $numberOfSituationsValue = $this->getValue($modifierCode, self::NUMBER_OF_SITUATIONS);
        if (!$numberOfSituationsValue) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new NumberOfSituations($numberOfSituationsValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Threshold|null
     */
    public function getThreshold(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $thresholdValues = $this->getValue($modifierCode, self::THRESHOLD);
        if (!$thresholdValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Threshold($thresholdValues);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|FormCode[]
     */
    public function getForms(ModifierCode $modifierCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $formValue) {
                return FormCode::getIt($formValue);
            },
            $this->getValue($modifierCode, self::FORMS)
        );
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|SpellTrait[]
     */
    public function getSpellTraits(ModifierCode $modifierCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $spellTraitAnnotation) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                return new SpellTrait($spellTraitAnnotation);
            },
            $this->getValue($modifierCode, self::TRAITS)
        );
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|ProfileCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierToGetProfilesFor
     */
    public function getProfiles(ModifierCode $modifierCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $profileValue) {
                    return ProfileCode::getIt($profileValue);
                },
                $this->getValue($modifierCode, self::PROFILES)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownModifierToGetProfilesFor("Given modifier code '{$modifierCode}' is unknown");
        }
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|FormulaCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierToGetFormulasFor
     */
    public function getFormulas(ModifierCode $modifierCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $formulaValue) {
                    return FormulaCode::getIt($formulaValue);
                },
                $this->getValue($modifierCode, self::FORMULAS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownModifierToGetFormulasFor("Given modifier code '{$modifierCode}' is unknown");
        }
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|ModifierCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierToGetParentModifiersFor
     */
    public function getParentModifiers(ModifierCode $modifierCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $modifierValue) {
                    return ModifierCode::getIt($modifierValue);
                },
                $this->getValue($modifierCode, self::PARENT_MODIFIERS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownModifierToGetParentModifiersFor(
                "Given modifier code '{$modifierCode}' is unknown"
            );
        }
    }

    /**
     * @param ModifierCode $modifierCode
     * @return array|ModifierCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierToGetChildModifiersFor
     */
    public function getChildModifiers(ModifierCode $modifierCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $modifierValue) {
                    return ModifierCode::getIt($modifierValue);
                },
                $this->getValue($modifierCode, self::CHILD_MODIFIERS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownModifierToGetChildModifiersFor(
                "Given modifier code '{$modifierCode}' is unknown"
            );
        }
    }

}