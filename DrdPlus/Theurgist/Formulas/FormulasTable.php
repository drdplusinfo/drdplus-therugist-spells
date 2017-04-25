<?php
namespace DrdPlus\Theurgist\Formulas;

use DrdPlus\Codes\TimeUnitCode;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Measurements\Time\TimeBonus;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use DrdPlus\Theurgist\Formulas\CastingParameters\Attack;
use DrdPlus\Theurgist\Formulas\CastingParameters\Brightness;
use DrdPlus\Theurgist\Formulas\CastingParameters\Evocation;
use DrdPlus\Theurgist\Formulas\CastingParameters\DetailLevel;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyLimit;
use DrdPlus\Theurgist\Formulas\CastingParameters\Duration;
use DrdPlus\Theurgist\Formulas\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Formulas\CastingParameters\Power;
use DrdPlus\Theurgist\Formulas\CastingParameters\Radius;
use DrdPlus\Theurgist\Formulas\CastingParameters\Realm;
use DrdPlus\Theurgist\Formulas\CastingParameters\SizeChange;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use Granam\Integer\IntegerObject;

class FormulasTable extends AbstractFileTable
{
    /**
     * @var Tables
     */
    private $tables;
    /**
     * @var ModifiersTable
     */
    private $modifiersTable;

    public function __construct(Tables $tables, ModifiersTable $modifiersTable)
    {
        $this->tables = $tables;
        $this->modifiersTable = $modifiersTable;
    }

    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/formulas.csv';
    }

    const REALM = 'realm';
    const AFFECTION = 'affection';
    const EVOCATION = 'evocation';
    const DIFFICULTY_LIMIT = 'difficulty_limit';
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
    const TRAITS = 'traits';
    const PROFILES = 'profiles';
    const MODIFIERS = 'modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::AFFECTION => self::ARRAY,
            self::EVOCATION => self::POSITIVE_INTEGER,
            self::DIFFICULTY_LIMIT => self::ARRAY,
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
     * @return Realm
     */
    public function getRealm(FormulaCode $formulaCode): Realm
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Realm($this->getValue($formulaCode, self::REALM));
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return Realm
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     */
    public function getRealmOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes
    ): Realm
    {
        $basicFormulaDifficultyLimit = $this->getDifficultyLimit($formulaCode);
        $maximalDifficultyHandledByFormula = $basicFormulaDifficultyLimit->getMaximal();
        $minimalPossibleRealm = $this->getRealm($formulaCode);
        $difficultyOfModifiedWithoutRealmChange = $this->getDifficultyOfModified(
            $formulaCode,
            $modifierCodes
        )->getValue();
        $highestRequiredRealmByModifiers = $this->modifiersTable->getHighestRequiredRealm($modifierCodes);
        if ($maximalDifficultyHandledByFormula >= $difficultyOfModifiedWithoutRealmChange
            && $minimalPossibleRealm->getValue() >= $highestRequiredRealmByModifiers->getValue()
        ) {
            return $minimalPossibleRealm;
        }
        $formulaAdditionByRealms = $basicFormulaDifficultyLimit->getAdditionByRealms();
        $difficultyHandledByAdditionalRealm = $formulaAdditionByRealms->getAddition();
        if ($difficultyHandledByAdditionalRealm <= 0) {
            // this should never happen, because every formula addition is currently greater than zero
            throw new Exceptions\CanNotBuildFormulaWithRequiredModification(
                "Formula {$formulaCode} with basic difficulty {$basicFormulaDifficultyLimit}"
                . " can not be build with difficulty {$difficultyOfModifiedWithoutRealmChange}"
                . " because of its addition by realms {$formulaAdditionByRealms}"
            );
        }
        $realmIncrementToHandleAdditionalDifficulty = $formulaAdditionByRealms->getRealmIncrement();
        while ($maximalDifficultyHandledByFormula < $difficultyOfModifiedWithoutRealmChange) {
            $maximalDifficultyHandledByFormula += $difficultyHandledByAdditionalRealm;
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $minimalPossibleRealm = $minimalPossibleRealm->add($realmIncrementToHandleAdditionalDifficulty);
        }
        // handled difficulty is enough, but realm is still needed higher
        /** @var Realm $minimalPossibleRealm */
        while ($minimalPossibleRealm->getValue() < $highestRequiredRealmByModifiers->getValue()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $minimalPossibleRealm = $minimalPossibleRealm->add($realmIncrementToHandleAdditionalDifficulty);
        }

        return $minimalPossibleRealm;
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Affection
     */
    public function getAffection(FormulaCode $formulaCode): Affection
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Affection($this->getValue($formulaCode, self::AFFECTION));
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return array|Affection[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     */
    public function getAffectionsOfModified(FormulaCode $formulaCode, array $modifierCodes): array
    {
        $formulaAffection = $this->getAffection($formulaCode);
        $summedAffections = [$formulaAffection->getAffectionPeriod()->getValue() => $formulaAffection];
        /** @var Affection $modifiersAffection */
        foreach ($this->modifiersTable->getAffectionsOfModifiers($modifierCodes) as $modifiersAffection) {
            $affectionPeriodValue = $modifiersAffection->getAffectionPeriod()->getValue();
            if (!array_key_exists($affectionPeriodValue, $summedAffections)) {
                $summedAffections[$affectionPeriodValue] = $modifiersAffection;
                continue;
            }
            /** @var Affection $summedAffection */
            $summedAffection = $summedAffections[$affectionPeriodValue];
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $summedAffections[$affectionPeriodValue] = new Affection([
                $summedAffection->getValue() + $modifiersAffection->getValue(),
                $affectionPeriodValue,
            ]);
        }

        return $summedAffections;
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
        return new Evocation($this->getValue($formulaCode, self::EVOCATION), $this->tables->getTimeTable());
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return TimeBonus
     */
    public function getEvocationOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes
    ): TimeBonus
    {
        // no modifier affects evocation time
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new TimeBonus(
            $this->getEvocation($formulaCode)->getValue(),
            $this->tables->getTimeTable()
        );
    }

    /**
     * Gives time in fact.
     * Currently every unmodified formula can be casted in one round.
     *
     * @param FormulaCode $formulaCode
     * @return Time
     */
    public function getCasting(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode): Time
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Time(1, TimeUnitCode::ROUND, $this->tables->getTimeTable());
    }

    /**
     * Gives time in fact
     *
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return Time
     */
    public function getCastingOfModified(FormulaCode $formulaCode, array $modifierCodes): Time
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $rounds = $this->getCasting($formulaCode)->getInUnit(TimeUnitCode::ROUND)->getValue()
            + $this->modifiersTable->sumCastingRoundsChange($modifierCodes)->getValue();

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Time($rounds, TimeUnitCode::ROUND, $this->tables->getTimeTable());
    }

    /**
     * @param FormulaCode $formulaCode
     * @return DifficultyLimit
     */
    public function getDifficultyLimit(FormulaCode $formulaCode): DifficultyLimit
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DifficultyLimit($this->getValue($formulaCode, self::DIFFICULTY_LIMIT));
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return IntegerObject
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     */
    public function getDifficultyOfModified(FormulaCode $formulaCode, array $modifierCodes): IntegerObject
    {
        return new IntegerObject(
            $this->getDifficultyLimit($formulaCode)->getMinimal()
            + $this->modifiersTable->sumDifficultyChanges($modifierCodes)->getValue()
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Radius|null
     */
    public function getRadius(FormulaCode $formulaCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $radiusValues = $this->getValue($formulaCode, self::RADIUS);
        if (!$radiusValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Radius($radiusValues, $this->tables->getDistanceTable());
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return DistanceBonus|null
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     */
    public function getRadiusOfModified(FormulaCode $formulaCode, array $modifierCodes)
    {
        $formulaRadius = $this->getRadius($formulaCode);
        if (!$formulaRadius) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DistanceBonus(
            $formulaRadius->getValue() + $this->modifiersTable->sumRadiusChange($modifierCodes)->getValue(),
            $this->tables->getDistanceTable()
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Duration
     */
    public function getDuration(FormulaCode $formulaCode): Duration
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Duration($this->getValue($formulaCode, self::DURATION), $this->tables->getTimeTable());
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
     * @param array|ModifierCode[] $modifierCodes
     * @return Power|null
     */
    public function getPowerOfModified(FormulaCode $formulaCode, array $modifierCodes)
    {
        $formulaPower = $this->getPower($formulaCode);
        if (!$formulaPower) {
            return null;
        }

        return $formulaPower->add($this->modifiersTable->sumPowerChange($modifierCodes)->getValue());
    }

    /**
     * @param FormulaCode $formulaCode
     * @return Attack|null
     */
    public function getAttack(FormulaCode $formulaCode)
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
     * @param array|ModifierCode[] $modifierCodes
     * @return Attack|null
     */
    public function getAttackOfModified(FormulaCode $formulaCode, array $modifierCodes)
    {
        $formulaAttack = $this->getAttack($formulaCode);
        if (!$formulaAttack) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $formulaAttack->add($this->modifiersTable->sumAttackChange($modifierCodes)->getValue());
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
     * @return DetailLevel|null
     */
    public function getDetailLevel(FormulaCode $formulaCode)
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
    public function getBrightness(FormulaCode $formulaCode)
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
     * @param array|ModifierCode[] $modifierCodes
     * @return Brightness|null
     */
    public function getBrightnessOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes
    )
    {
        // no modifier affects brightness
        return $this->getBrightness($formulaCode);
    }

    /**
     * @param FormulaCode $formulaCode
     * @return SpellSpeed|null
     */
    public function getSpellSpeed(FormulaCode $formulaCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $speedValues = $this->getValue($formulaCode, self::SPELL_SPEED);
        if (!$speedValues) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpellSpeed($speedValues, $this->tables->getSpeedTable());
    }

    /**
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return SpeedBonus|null
     */
    public function getSpellSpeedOfModified(FormulaCode $formulaCode, array $modifierCodes)
    {
        $formulaSpeed = $this->getSpellSpeed($formulaCode);
        if (!$formulaSpeed) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new SpeedBonus(
            $formulaSpeed->getValue()
            + $this->modifiersTable->sumSpellSpeedChange($modifierCodes)->getValue(),
            $this->tables->getSpeedTable()
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return EpicenterShift|null
     */
    public function getEpicenterShift(FormulaCode $formulaCode)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $epicenterShift = $this->getValue($formulaCode, self::EPICENTER_SHIFT);
        if (!$epicenterShift) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift($epicenterShift, $this->tables->getDistanceTable());
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
     * @return array|SpellTrait[]
     */
    public function getSpellTraits(FormulaCode $formulaCode): array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return array_map(
            function (string $spellTraitAnnotation) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                return new SpellTrait($spellTraitAnnotation);
            },
            $this->getValue($formulaCode, self::TRAITS)
        );
    }

    /**
     * @param FormulaCode $formulaCode
     * @return array|ProfileCode[]
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetProfilesFor
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
     * @throws \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetModifiersFor
     */
    public function getModifiers(FormulaCode $formulaCode): array
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

    /**
     * Transposition can shift epicenter.
     *
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @return DistanceBonus|null
     */
    public function getEpicenterShiftOfModified(FormulaCode $formulaCode, array $modifierCodes)
    {
        $formulaEpicenterShift = $this->getEpicenterShift($formulaCode);
        if (!$formulaEpicenterShift) {
            $formulaEpicenterShiftValue = 0;
            if (!$this->modifiersTable->isEpicenterShifted($modifierCodes)) {
                return null; // no shift at all
            }
        } else {
            $formulaEpicenterShiftValue = $formulaEpicenterShift->getValue();
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new DistanceBonus(
            $formulaEpicenterShiftValue
            + $this->modifiersTable->sumEpicenterShiftChange($modifierCodes)->getValue(),
            $this->tables->getDistanceTable()
        );
    }

}