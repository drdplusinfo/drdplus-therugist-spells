<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Difficulty;
use DrdPlus\Theurgist\Formulas\CastingParameters\Duration;
use DrdPlus\Theurgist\Formulas\CastingParameters\Power;
use DrdPlus\Theurgist\Formulas\CastingParameters\Radius;
use DrdPlus\Theurgist\Formulas\CastingParameters\SizeChange;
use Granam\Integer\NegativeInteger;
use Granam\Integer\NegativeIntegerObject;
use Granam\Integer\PositiveInteger;
use Granam\Integer\PositiveIntegerObject;

class FormulasTable extends AbstractFileTable
{
    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/formulas.csv';
    }

    const REALM = 'realm';
    const AFFECTION = 'affection';
    const CASTING = 'casting';
    const DIFFICULTY = 'difficulty';
    const RADIUS = 'radius';
    const DURATION = 'duration';
    const POWER = 'power';
    const SIZE_CHANGE = 'size_change';
    const DETAIL_LEVEL = 'detail_level';
    const BRIGHTNESS = 'brightness';
    const SPEED = 'speed';
    const ATTACK = 'attack';
    const TRANSPOSITION = 'transposition';
    const FORMS = 'forms';
    const TRAITS = 'traits';
    const PROFILES = 'profiles';
    const MODIFIERS = 'modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::AFFECTION => self::NEGATIVE_INTEGER,
            self::CASTING => self::POSITIVE_INTEGER,
            self::DIFFICULTY => self::ARRAY,
            self::RADIUS => self::ARRAY,
            self::DURATION => self::ARRAY,
            self::POWER => self::ARRAY,
            self::SIZE_CHANGE => self::ARRAY,
            self::DETAIL_LEVEL => self::ARRAY,
            self::BRIGHTNESS => self::ARRAY,
            self::SPEED => self::ARRAY,
            self::ATTACK => self::ARRAY,
            self::TRANSPOSITION => self::ARRAY,
            self::FORMS => self::ARRAY,
            self::TRAITS => self::ARRAY,
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
     * @return PositiveInteger
     */
    public function getRealm(FormulaCode $formulaCode): PositiveInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new PositiveIntegerObject($this->getValue($formulaCode, self::REALM));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return NegativeInteger
     */
    public function getAffection(FormulaCode $formulaCode): NegativeInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new NegativeIntegerObject($this->getValue($formulaCode, self::AFFECTION));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return PositiveInteger
     */
    public function getCasting(FormulaCode $formulaCode): PositiveInteger
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new PositiveIntegerObject($this->getValue($formulaCode, self::CASTING));
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Difficulty
     */
    public function getDifficulty(FormulaCode $formulaCode): Difficulty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Difficulty($this->getValue($formulaCode, self::DIFFICULTY));
    }

    /**
     * @param FormulaCode $formulaCode
     * @param DistanceTable $distanceTable
     * @return Radius|null
     */
    public function getRadius(FormulaCode $formulaCode, DistanceTable $distanceTable)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $radiusValues = $this->getValue($formulaCode, self::RADIUS);
        if (!$radiusValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius($radiusValues, $distanceTable);
    }

    /**
     * @param FormulaCode $formulaCode
     * @param TimeTable $timeTable
     * @return Duration
     */
    public function getDuration(FormulaCode $formulaCode, TimeTable $timeTable): Duration
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Duration($this->getValue($formulaCode, self::DURATION), $timeTable);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Power|null
     */
    public function getPower(FormulaCode $formulaCode)
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
     * @return SizeChange|null
     */
    public function getSizeChange(FormulaCode $formulaCode)
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
     * @return array|ProfileCode[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetProfilesFor
     */
    public function getProfilesForFormula(FormulaCode $formulaCode): array
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetModifiersFor
     */
    public function getModifiersForFormula(FormulaCode $formulaCode): array
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