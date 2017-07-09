<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\RealmsAffection;
use DrdPlus\Theurgist\Spells\SpellParameters\Attack;
use DrdPlus\Theurgist\Spells\SpellParameters\Brightness;
use DrdPlus\Theurgist\Spells\SpellParameters\Evocation;
use DrdPlus\Theurgist\Spells\SpellParameters\DetailLevel;
use DrdPlus\Theurgist\Spells\SpellParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\Duration;
use DrdPlus\Theurgist\Spells\SpellParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\SpellParameters\Power;
use DrdPlus\Theurgist\Spells\SpellParameters\Radius;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\SizeChange;
use DrdPlus\Theurgist\Spells\SpellParameters\SpellSpeed;

class FormulasTable extends AbstractFileTable
{
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/formulas.csv';
    }

    const REALM = 'realm';
    const REALMS_AFFECTION = 'realms_affection';
    const EVOCATION = 'evocation';
    const FORMULA_DIFFICULTY = 'formula_difficulty';
    const RADIUS = 'radius';
    const DURATION = 'duration';
    const POWER = 'power';
    const ATTACK = 'attack';
    const SIZE_CHANGE = 'size_change';
    const DETAIL_LEVEL = 'detail_level';
    const BRIGHTNESS = 'brightness';
    const SPELL_SPEED = 'spell_speed';
    const EPICENTER_SHIFT = 'epicenter_shift';
    const FORMS = 'forms';
    const SPELL_TRAITS = 'spell_traits';
    const PROFILES = 'profiles';
    const MODIFIERS = 'modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::REALMS_AFFECTION => self::ARRAY,
            self::EVOCATION => self::ARRAY,
            self::FORMULA_DIFFICULTY => self::ARRAY,
            self::RADIUS => self::ARRAY,
            self::DURATION => self::ARRAY,
            self::POWER => self::ARRAY,
            self::ATTACK => self::ARRAY,
            self::SIZE_CHANGE => self::ARRAY,
            self::DETAIL_LEVEL => self::ARRAY,
            self::BRIGHTNESS => self::ARRAY,
            self::SPELL_SPEED => self::ARRAY,
            self::EPICENTER_SHIFT => self::ARRAY,
            self::FORMS => self::ARRAY,
            self::SPELL_TRAITS => self::ARRAY,
            self::PROFILES => self::ARRAY,
            self::MODIFIERS => self::ARRAY,
        ];
    }

    const FORMULA = 'formula';

    protected function getRowsHeader(): array
    {
        return [
            self::FORMULA,
        ];
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Realm
     */
    public function getRealm(FormulaCode $formulaCode): Realm
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Realm($this->getValue($formulaCode, self::REALM));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return RealmsAffection
     */
    public function getRealmsAffection(FormulaCode $formulaCode): RealmsAffection
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new RealmsAffection($this->getValue($formulaCode, self::REALMS_AFFECTION));
    }

    /**
     * Time needed to invoke (assemble) a spell. Gives time bonus value in fact.
     *
     * @param FormulaCode $formulaCode
     * @return Evocation
     */
    public function getEvocation(FormulaCode $formulaCode): Evocation
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Evocation($this->getValue($formulaCode, self::EVOCATION));
    }

    /**
     * Gives time in fact.
     * Currently every unmodified formula can be casted in one round.
     *
     * @param FormulaCode $formulaCode
     * @return CastingRounds
     */
    public function getCastingRounds(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode): CastingRounds
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new CastingRounds([1]);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return FormulaDifficulty
     */
    public function getFormulaDifficulty(FormulaCode $formulaCode): FormulaDifficulty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new FormulaDifficulty($this->getValue($formulaCode, self::FORMULA_DIFFICULTY));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Radius|null
     */
    public function getRadius(FormulaCode $formulaCode):? Radius
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $radiusValues = $this->getValue($formulaCode, self::RADIUS);
        if (!$radiusValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius($radiusValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Duration
     */
    public function getDuration(FormulaCode $formulaCode): Duration
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Duration($this->getValue($formulaCode, self::DURATION));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Power|null
     */
    public function getPower(FormulaCode $formulaCode):? Power
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $powerValues = $this->getValue($formulaCode, self::POWER);
        if (!$powerValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Power($powerValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Attack|null
     */
    public function getAttack(FormulaCode $formulaCode):? Attack
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $attackValues = $this->getValue($formulaCode, self::ATTACK);
        if (!$attackValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Attack($attackValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return SizeChange|null
     */
    public function getSizeChange(FormulaCode $formulaCode):? SizeChange
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $sizeChangeValues = $this->getValue($formulaCode, self::SIZE_CHANGE);
        if (!$sizeChangeValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SizeChange($sizeChangeValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return DetailLevel|null
     */
    public function getDetailLevel(FormulaCode $formulaCode):? DetailLevel
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $detailLevelValues = $this->getValue($formulaCode, self::DETAIL_LEVEL);
        if (!$detailLevelValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DetailLevel($detailLevelValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Brightness|null
     */
    public function getBrightness(FormulaCode $formulaCode):? Brightness
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $brightnessValues = $this->getValue($formulaCode, self::BRIGHTNESS);
        if (!$brightnessValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Brightness($brightnessValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return SpellSpeed|null
     */
    public function getSpellSpeed(FormulaCode $formulaCode):? SpellSpeed
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $speedValues = $this->getValue($formulaCode, self::SPELL_SPEED);
        if (!$speedValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpellSpeed($speedValues);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return EpicenterShift|null
     */
    public function getEpicenterShift(FormulaCode $formulaCode):? EpicenterShift
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $epicenterShift = $this->getValue($formulaCode, self::EPICENTER_SHIFT);
        if (!$epicenterShift) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift($epicenterShift);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|FormCode[]
     */
    public function getForms(FormulaCode $formulaCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $formValue) {
                return FormCode::getIt($formValue);
            },
            $this->getValue($formulaCode, self::FORMS)
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|SpellTraitCode[]
     */
    public function getSpellTraitCodes(FormulaCode $formulaCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $spellTraitValue) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                return SpellTraitCode::getIt($spellTraitValue);
            },
            $this->getValue($formulaCode, self::SPELL_TRAITS)
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|ProfileCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaToGetProfilesFor
     */
    public function getProfiles(FormulaCode $formulaCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $profileValue) {
                    return ProfileCode::getIt($profileValue);
                },
                $this->getValue($formulaCode, self::PROFILES)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownFormulaToGetProfilesFor("Given formula code '{$formulaCode}' is unknown");
        }
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|ModifierCode[]
     * @throws \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaToGetModifiersFor
     */
    public function getModifierCodes(FormulaCode $formulaCode): array
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return array_map(
                function (string $modifierValue) {
                    return ModifierCode::getIt($modifierValue);
                },
                $this->getValue($formulaCode, self::MODIFIERS)
            );
        } catch (RequiredRowNotFound $requiredRowNotFound) {
            throw new Exceptions\UnknownFormulaToGetModifiersFor("Given formula code '{$formulaCode}' is unknown");
        }
    }
}