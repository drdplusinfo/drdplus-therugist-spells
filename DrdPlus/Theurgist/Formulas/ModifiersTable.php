<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use DrdPlus\Theurgist\Formulas\CastingParameters\Attack;
use DrdPlus\Theurgist\Formulas\CastingParameters\Casting;
use DrdPlus\Theurgist\Formulas\CastingParameters\Conditions;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyChange;
use DrdPlus\Theurgist\Formulas\CastingParameters\Grafts;
use DrdPlus\Theurgist\Formulas\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Formulas\CastingParameters\Points;
use DrdPlus\Theurgist\Formulas\CastingParameters\Power;
use DrdPlus\Theurgist\Formulas\CastingParameters\Quality;
use DrdPlus\Theurgist\Formulas\CastingParameters\Radius;
use DrdPlus\Theurgist\Formulas\CastingParameters\Realm;
use DrdPlus\Theurgist\Formulas\CastingParameters\Resistance;
use DrdPlus\Theurgist\Formulas\CastingParameters\NumberOfSituations;
use DrdPlus\Theurgist\Formulas\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Formulas\CastingParameters\Speed;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\CastingParameters\Threshold;
use Granam\Integer\IntegerInterface;
use Granam\Integer\IntegerObject;

class ModifiersTable extends AbstractFileTable
{
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
    const CASTING = 'casting';
    const DIFFICULTY_CHANGE = 'difficulty_change';
    const RADIUS = 'radius';
    const EPICENTER_SHIFT = 'epicenter_shift';
    const POWER = 'power';
    const ATTACK = 'attack';
    const GRAFTS = 'grafts';
    const SPEED = 'speed';
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
            self::CASTING => self::POSITIVE_INTEGER,
            self::DIFFICULTY_CHANGE => self::POSITIVE_INTEGER,
            self::RADIUS => self::ARRAY,
            self::EPICENTER_SHIFT => self::ARRAY,
            self::POWER => self::ARRAY,
            self::ATTACK => self::ARRAY,
            self::GRAFTS => self::ARRAY,
            self::SPEED => self::ARRAY,
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
     * @param ModifierCode $modifierCode
     * @param TimeTable $timeTable
     * @return Casting
     */
    public function getCasting(ModifierCode $modifierCode, TimeTable $timeTable): Casting
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Casting($this->getValue($modifierCode, self::CASTING), $timeTable);
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
     * @param ModifierCode $modifierCode
     * @param DistanceTable $distanceTable
     * @return Radius|null
     */
    public function getRadius(ModifierCode $modifierCode, DistanceTable $distanceTable)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $radiusValues = $this->getValue($modifierCode, self::RADIUS);
        if (!$radiusValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius($radiusValues, $distanceTable);
    }

    /**
     * @param ModifierCode $modifierCode
     * @param DistanceTable $distanceTable
     * @return EpicenterShift|null
     */
    public function getEpicenterShift(ModifierCode $modifierCode, DistanceTable $distanceTable)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shiftValues = $this->getValue($modifierCode, self::EPICENTER_SHIFT);
        if (!$shiftValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift($shiftValues, $distanceTable);
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
     * @return Speed|null
     */
    public function getSpeed(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $speedValues = $this->getValue($modifierCode, self::SPEED);
        if (!$speedValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Speed($speedValues);
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetProfilesFor
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetFormulasFor
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetParentModifiersFor
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetChildModifiersFor
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

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerInterface
     */
    public function sumDifficultyChanges(array $modifierCodes): IntegerInterface
    {
        return new IntegerObject(
            array_sum(
                array_map(function ($modifierCodesOrCode) {
                    /** @var ModifierCode $modifierCodesOrCode */
                    return $this->getDifficultyChange($modifierCodesOrCode)->getValue();
                }, $this->toFlatArray($modifierCodes))
            )
        );
    }

    /**
     * @param array $items
     * @return array
     */
    private function toFlatArray(array $items): array
    {
        $flat = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ($this->toFlatArray($item) as $subItem) {
                    $flat[] = $subItem;
                }
            } else {
                $flat[] = $item;
            }
        }

        return $flat;
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
     * @param array|ModifierCode[] $modifierCodes
     * @param DistanceTable $distanceTable
     * @return DistanceBonus
     */
    public function sumRadii(array $modifierCodes, DistanceTable $distanceTable): DistanceBonus
    {
        $radiusValue = 0;
        foreach ($modifierCodes as $modifierCode) {
            $radius = $this->getRadius($modifierCode, $distanceTable);
            if (!$radius) {
                continue;
            }
            $radiusValue += $radius->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DistanceBonus($radiusValue, $distanceTable);
    }

    /**
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     */
    public function sumPowers(array $modifierCodes): IntegerObject
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
     * @param array|ModifierCode[] $modifierCodes
     * @param DistanceTable $distanceTable
     * @return DistanceBonus
     */
    public function sumEpicenterShifts(array $modifierCodes, DistanceTable $distanceTable): DistanceBonus
    {
        $shiftValues = [];
        foreach ($modifierCodes as $modifierCode) {
            $shift = $this->getEpicenterShift($modifierCode, $distanceTable);
            if (!$shift) {
                continue;
            }
            $shiftValues[] = $shift->getValue();
        }

        if (count($shiftValues) === 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return new DistanceBonus(-40, $distanceTable); // lowest possible (0.01 meter)
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DistanceBonus(array_sum($shiftValues), $distanceTable);
    }
}