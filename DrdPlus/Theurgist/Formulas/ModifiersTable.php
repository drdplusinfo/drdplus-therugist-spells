<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Attack;
use DrdPlus\Theurgist\Formulas\CastingParameters\Conditions;
use DrdPlus\Theurgist\Formulas\CastingParameters\Difficulty;
use DrdPlus\Theurgist\Formulas\CastingParameters\Grafts;
use DrdPlus\Theurgist\Formulas\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Formulas\CastingParameters\Points;
use DrdPlus\Theurgist\Formulas\CastingParameters\Power;
use DrdPlus\Theurgist\Formulas\CastingParameters\Quality;
use DrdPlus\Theurgist\Formulas\CastingParameters\Radius;
use DrdPlus\Theurgist\Formulas\CastingParameters\Situations;
use DrdPlus\Theurgist\Formulas\CastingParameters\Speed;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\CastingParameters\Threshold;
use Granam\Integer\NegativeInteger;
use Granam\Integer\NegativeIntegerObject;
use Granam\Integer\PositiveInteger;
use Granam\Integer\PositiveIntegerObject;

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
    const DIFFICULTY = 'difficulty';
    const RADIUS = 'radius';
    const POWER = 'power';
    const ATTACK = 'attack';
    const GRAFTS = 'grafts';
    const SPEED = 'speed';
    const POINTS = 'points';
    const INVISIBILITY = 'invisibility';
    const QUALITY = 'quality';
    const CONDITIONS = 'conditions';
    const RESISTANCE = 'resistance';
    const SITUATIONS = 'situations';
    const THRESHOLD = 'threshold';
    const FORMS = 'forms';
    const TRAITS = 'traits';
    const PROFILES = 'profiles';
    const FORMULAS = 'formulas';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::AFFECTION => self::NEGATIVE_INTEGER,
            self::AFFECTION_TYPE => self::STRING,
            self::CASTING => self::POSITIVE_INTEGER,
            self::DIFFICULTY => self::POSITIVE_INTEGER,
            self::RADIUS => self::ARRAY,
            self::POWER => self::ARRAY,
            self::ATTACK => self::ARRAY,
            self::GRAFTS => self::ARRAY,
            self::SPEED => self::ARRAY,
            self::POINTS => self::ARRAY,
            self::INVISIBILITY => self::ARRAY,
            self::QUALITY => self::ARRAY,
            self::CONDITIONS => self::ARRAY,
            self::RESISTANCE => self::ARRAY,
            self::SITUATIONS => self::ARRAY,
            self::THRESHOLD => self::ARRAY,
            self::FORMS => self::ARRAY,
            self::TRAITS => self::ARRAY,
            self::PROFILES => self::ARRAY,
            self::FORMULAS => self::ARRAY,
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
     * @return PositiveInteger
     */
    public function getRealm(ModifierCode $modifierCode): PositiveInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new PositiveIntegerObject($this->getValue($modifierCode, self::REALM));
    }

    /**
     * @param ModifierCode $modifierCode
     * @return NegativeInteger
     */
    public function getAffection(ModifierCode $modifierCode): NegativeInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new NegativeIntegerObject($this->getValue($modifierCode, self::AFFECTION));
    }

    /**
     * @param ModifierCode $modifierCode
     * @return PositiveInteger
     */
    public function getCasting(ModifierCode $modifierCode): PositiveInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new PositiveIntegerObject($this->getValue($modifierCode, self::CASTING));
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Difficulty
     */
    public function getDifficulty(ModifierCode $modifierCode): Difficulty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Difficulty($this->getValue($modifierCode, self::DIFFICULTY));
    }

    /**
     * @param ModifierCode $modifierCode
     * @param DistanceTable $distanceTable
     * @return Radius|null
     */
    public function getRadius(FormulaCode $modifierCode, DistanceTable $distanceTable)
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
     * @return Situations|null
     */
    public function getResistance(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $resistanceValue = $this->getValue($modifierCode, self::RESISTANCE);
        if (!$resistanceValue) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Situations($resistanceValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Situations|null
     */
    public function getSituations(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $situationsValue = $this->getValue($modifierCode, self::SITUATIONS);
        if (!$situationsValue) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Situations($situationsValue);
    }

    /**
     * @param ModifierCode $modifierCode
     * @return Threshold|null
     */
    public function getThreshold(ModifierCode $modifierCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $situationsValue = $this->getValue($modifierCode, self::THRESHOLD);
        if (!$situationsValue) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Threshold($situationsValue);
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
    public function getProfilesForModifier(ModifierCode $modifierCode): array
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
    public function getFormulasForModifier(ModifierCode $modifierCode): array
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
}