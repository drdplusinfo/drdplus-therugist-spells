<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Codes\TimeUnitCode;
use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Partials\AbstractFileTable;
use DrdPlus\Tables\Partials\Exceptions\RequiredRowNotFound;
use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Affection;
use DrdPlus\Theurgist\Spells\CastingParameters\Attack;
use DrdPlus\Theurgist\Spells\CastingParameters\Brightness;
use DrdPlus\Theurgist\Spells\CastingParameters\Evocation;
use DrdPlus\Theurgist\Spells\CastingParameters\DetailLevel;
use DrdPlus\Theurgist\Spells\CastingParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\CastingParameters\Duration;
use DrdPlus\Theurgist\Spells\CastingParameters\EpicenterShift;
use DrdPlus\Theurgist\Spells\CastingParameters\Power;
use DrdPlus\Theurgist\Spells\CastingParameters\Radius;
use DrdPlus\Theurgist\Spells\CastingParameters\Realm;
use DrdPlus\Theurgist\Spells\CastingParameters\SizeChange;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellTrait;
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
    /**
     * @var SpellTraitsTable
     */
    private $spellTraitsTable;

    /**
     * @param Tables $tables
     * @param ModifiersTable $modifiersTable
     * @param SpellTraitsTable $spellTraitsTable
     */
    public function __construct(Tables $tables, ModifiersTable $modifiersTable, SpellTraitsTable $spellTraitsTable)
    {
        $this->tables = $tables;
        $this->modifiersTable = $modifiersTable;
        $this->spellTraitsTable = $spellTraitsTable;
    }

    protected function getDataFileName(): string
    {
        return __DIR__ . '/data/formulas.csv';
    }

    const REALM = 'realm';
    const AFFECTION = 'affection';
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
    const TRAITS = 'traits';
    const PROFILES = 'profiles';
    const MODIFIERS = 'modifiers';

    protected function getExpectedDataHeaderNamesToTypes(): array
    {
        return [
            self::REALM => self::POSITIVE_INTEGER,
            self::AFFECTION => self::ARRAY,
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Realm
     */
    public function getRealmOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): Realm
    {
        $basicFormulaDifficulty = $this->getFormulaDifficulty($formulaCode);
        $maximalDifficultyOfBasicFormula = $basicFormulaDifficulty->getMaximal();
        $formulaBasicRealm = $this->getRealm($formulaCode);
        $difficultyOfModifiedValue = $this->getFormulaDifficultyOfModified(
            $formulaCode,
            $modifierCodes,
            $spellTraitCodes
        )->getValue();
        $highestRequiredRealmByModifiers = $this->modifiersTable->getHighestRequiredRealm($modifierCodes);
        if ($maximalDifficultyOfBasicFormula >= $difficultyOfModifiedValue
            && $formulaBasicRealm->getValue() >= $highestRequiredRealmByModifiers->getValue()
        ) {
            // formula is able to handle requirements from its lowest possible realm
            return $formulaBasicRealm;
        }
        $missingDifficulty = $difficultyOfModifiedValue - $maximalDifficultyOfBasicFormula;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $requiredAdditionByRealms = $basicFormulaDifficulty->getFormulaDifficultyAddition()->add($missingDifficulty);
        $byDifficultyRequiredRealm = $formulaBasicRealm->add($requiredAdditionByRealms->getCurrentRealmsIncrement());
        if ($byDifficultyRequiredRealm->getValue() >= $highestRequiredRealmByModifiers->getValue()) {
            return $byDifficultyRequiredRealm;
        }

        // handled difficulty was enough, but realm is still required higher by modifiers
        return $highestRequiredRealmByModifiers;
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return array|Affection[]
     */
    public function getAffectionsOfModified(/** @noinspection PhpUnusedParameterInspection */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): array
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Evocation
     */
    public function getEvocationOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): Evocation
    {
        return $this->getEvocation($formulaCode);
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Time
     */
    public function getCastingOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): Time
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $rounds = $this->getCasting($formulaCode)->getInUnit(TimeUnitCode::ROUND)->getValue()
            + $this->modifiersTable->sumCastingRoundsChange($modifierCodes)->getValue();

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Time($rounds, TimeUnitCode::ROUND, $this->tables->getTimeTable());
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
     * @param array|ModifierCode[] $modifierCodes
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return FormulaDifficulty
     */
    public function getFormulaDifficultyOfModified(
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): FormulaDifficulty
    { // todo give ModifiedDifficulty object as IntegerObject or something like that
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->getFormulaDifficulty($formulaCode)->setAddition(
            +$this->modifiersTable->sumDifficultyChanges($modifierCodes)->getValue()
            + $this->spellTraitsTable->sumDifficultyChanges($spellTraitCodes)->getValue()
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Radius|null
     */
    public function getRadiusOfModified(/** @noinspection PhpUnusedParameterInspection */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
    {
        $formulaRadius = $this->getRadius($formulaCode);
        if (!$formulaRadius) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $formulaRadius->setAddition(
            $this->modifiersTable->sumRadiusChange($modifierCodes)->getValue()
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
     * @param array|ModifierCode[] $modifierCodes
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Duration
     */
    public function getDurationOfModified(/** @noinspection PhpUnusedParameterInspection */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    ): Duration
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Power|null
     */
    public function getPowerOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
    {
        $formulaPower = $this->getPower($formulaCode);
        if (!$formulaPower) {
            return null;
        }

        return $formulaPower->setAddition($this->modifiersTable->sumPowerChanges($modifierCodes)->getValue());
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return IntegerObject|null
     */
    public function getAttackOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes = []
    )
    {
        $formulaAttack = $this->getAttack($formulaCode);
        if (!$formulaAttack) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new IntegerObject(
            $formulaAttack->getValue()
            + $this->modifiersTable->sumAttackChange($modifierCodes, [] /* $modifiersAttackAdditions */)->getValue()
        );
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
     * @param array|ModifierCode[] $modifierCodes
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return SizeChange|null
     */
    public function getSizeChangeOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
    {
        return $this->getSizeChange($formulaCode);
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
     * @param array|ModifierCode[] $modifierCodes
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return DetailLevel|null
     */
    public function getDetailLevelOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return Brightness|null
     */
    public function getBrightnessOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
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
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return SpellSpeed|null
     */
    public function getSpellSpeedOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
    {
        $formulaSpeed = $this->getSpellSpeed($formulaCode);
        if (!$formulaSpeed) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $formulaSpeed->setAddition($this->modifiersTable->sumSpellSpeedChange($modifierCodes)->getValue());
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
     * Transposition can shift epicenter.
     *
     * @param FormulaCode $formulaCode
     * @param array|ModifierCode[] $modifierCodes
     * @param array|SpellTraitCode[] $spellTraitCodes
     * @return EpicenterShift|null
     */
    public function getEpicenterShiftOfModified(/** @noinspection PhpUnusedParameterInspection to keep same interface with others */
        FormulaCode $formulaCode,
        array $modifierCodes,
        array $spellTraitCodes
    )
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

        if ($formulaEpicenterShift) {
            return $formulaEpicenterShift->setAddition($this->modifiersTable->sumEpicenterShiftChange($modifierCodes)->getValue());
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new EpicenterShift(
            [
                $formulaEpicenterShiftValue + $this->modifiersTable->sumEpicenterShiftChange($modifierCodes)->getValue(),
                0 // no addition by realm possible for this formula
            ],
            $this->tables->getDistanceTable()
        );
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
}