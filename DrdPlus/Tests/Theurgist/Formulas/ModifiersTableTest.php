<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Tables\Measurements\Time\TimeTable;
use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\AffectionPeriodCode;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use DrdPlus\Theurgist\Formulas\CastingParameters\Casting;
use DrdPlus\Theurgist\Formulas\CastingParameters\Realm;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use DrdPlus\Theurgist\Formulas\ModifiersTable;
use DrdPlus\Theurgist\Formulas\ProfilesTable;
use Granam\Integer\IntegerInterface;
use Granam\Integer\IntegerObject;

class ModifiersTableTest extends AbstractTheurgistTableTest
{

    /**
     * @test
     */
    public function I_can_get_every_mandatory_parameter()
    {
        $mandatoryParameters = ['realm', 'difficulty_change'];
        foreach ($mandatoryParameters as $mandatoryParameter) {
            $this->I_can_get_mandatory_parameter($mandatoryParameter, ModifierCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_every_optional_parameter()
    {
        /**
         * @see ModifiersTable::getAffection()
         * @see ModifiersTable::getCasting()
         * @see ModifiersTable::getRadius()
         * @see ModifiersTable::getEpicenterShift()
         * @see ModifiersTable::getPower()
         * @see ModifiersTable::getAttack()
         * @see ModifiersTable::getGrafts()
         * @see ModifiersTable::getSpellSpeed()
         * @see ModifiersTable::getPoints()
         * @see ModifiersTable::getInvisibility()
         * @see ModifiersTable::getQuality()
         * @see ModifiersTable::getConditions()
         * @see ModifiersTable::getResistance()
         * @see ModifiersTable::getNumberOfSituations()
         * @see ModifiersTable::getThreshold()
         */
        $optionalParameters = [
            'affection', 'casting', 'radius', 'epicenter_shift', 'power', 'attack', 'grafts', 'spell_speed', 'points',
            'invisibility', 'quality', 'conditions', 'resistance', 'number_of_situations', 'threshold',
        ];
        foreach ($optionalParameters as $optionalParameter) {
            $this->I_can_get_optional_parameter($optionalParameter, ModifierCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_forms()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $forms = $modifiersTable->getForms(ModifierCode::getIt($modifierValue));
            $formValues = [];
            foreach ($forms as $form) {
                self::assertInstanceOf(FormCode::class, $form);
                $formValues[] = $form->getValue();
            }
            self::assertSame($formValues, array_unique($formValues));
            sort($formValues);
            $expectedFormValues = $this->getExpectedFormValues($modifierValue);
            sort($expectedFormValues);
            self::assertEquals($expectedFormValues, $formValues, "Expected different forms for '{$modifierValue}'");
        }
    }

    private static $excludedFormValues = [
        ModifierCode::COLOR => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::GATE => ['indirect', 'volume', 'beam', 'intangible', 'invisible', 'by_formula'],
        ModifierCode::EXPLOSION => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::FILTER => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::WATCHER => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::THUNDER => ['direct', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        ModifierCode::INTERACTIVE_ILLUSION => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::HAMMER => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::CAMOUFLAGE => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::INVISIBILITY => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::MOVEMENT => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::BREACH => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::RECEPTOR => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::STEP_TO_FUTURE => ['indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible'],
        ModifierCode::STEP_TO_PAST => ['indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible'],
        ModifierCode::TRANSPOSITION => ['direct', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        ModifierCode::RELEASE => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
        ModifierCode::FRAGRANCE => ['direct', 'indirect', 'volume', 'planar', 'beam', 'tangible', 'intangible', 'visible', 'invisible', 'by_formula'],
    ];

    /**
     * @param string $modifierValue
     * @return array
     */
    private function getExpectedFormValues(string $modifierValue): array
    {
        $excludedFormValues = self::$excludedFormValues[$modifierValue];

        return array_diff(FormCode::getPossibleValues(), $excludedFormValues);
    }

    /**
     * @test
     */
    public function I_can_get_spell_traits()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $spellTraits = $modifiersTable->getSpellTraits(ModifierCode::getIt($modifierValue));
            /** @var array|string[] $expectedTraitValues */
            $expectedTraitValues = $this->getValueFromTable($modifiersTable, $modifierValue, 'traits');
            $expectedSpellTraits = [];
            foreach ($expectedTraitValues as $expectedTraitValue) {
                $expectedSpellTraits[] = new SpellTrait($expectedTraitValue);
            }
            self::assertEquals($expectedSpellTraits, $spellTraits);

            $spellSpellTraitCodeValues = [];
            foreach ($spellTraits as $spellTrait) {
                self::assertInstanceOf(SpellTrait::class, $spellTrait);
                $spellSpellTraitCodeValues[] = $spellTrait->getSpellTraitCode()->getValue();
            }
            self::assertSame($spellSpellTraitCodeValues, array_unique($spellSpellTraitCodeValues));
            sort($spellSpellTraitCodeValues);
            $expectedSpellTraitCodeValues = $this->getExpectedSpellTraitCodeValues($modifierValue);
            sort($expectedSpellTraitCodeValues);
            self::assertEquals($expectedSpellTraitCodeValues, $spellSpellTraitCodeValues, "Expected different traits for '{$modifierValue}'");
        }
    }

    private static $excludedTraitValues = [
        ModifierCode::COLOR => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::GATE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::EXPLOSION => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::FILTER => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::WATCHER => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::THUNDER => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::INTERACTIVE_ILLUSION => [SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::HAMMER => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::CAMOUFLAGE => [SpellTraitCode::AFFECTING, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::INVISIBILITY => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::MOVEMENT => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::BREACH => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::RECEPTOR => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::STEP_TO_FUTURE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::STEP_TO_PAST => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::TRANSPOSITION => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::RELEASE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        ModifierCode::FRAGRANCE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
    ];

    /**
     * @param string $formulaValue
     * @return array
     */
    private function getExpectedSpellTraitCodeValues(string $formulaValue): array
    {
        $excludedTraitValues = self::$excludedTraitValues[$formulaValue];

        return array_diff(SpellTraitCode::getPossibleValues(), $excludedTraitValues);
    }

    /**
     * @test
     */
    public function I_can_get_formulas_for_modifier()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $formulaCodes = $modifiersTable->getFormulas(ModifierCode::getIt($modifierValue));
            self::assertTrue(is_array($formulaCodes));
            if (in_array($modifierValue, [ModifierCode::STEP_TO_PAST, ModifierCode::STEP_TO_FUTURE], true)) {
                self::assertEmpty($formulaCodes);
            } else {
                self::assertNotEmpty($formulaCodes, 'Expected some formulas for modifier ' . $modifierValue);
            }
            $collectedFormulaValues = [];
            foreach ($formulaCodes as $formulaCode) {
                self::assertInstanceOf(FormulaCode::class, $formulaCode);
                $collectedFormulaValues[] = $formulaCode->getValue();
            }
            sort($collectedFormulaValues);
            $expectedFormulaValues = $this->getExpectedFormulaValues($modifierValue);
            sort($expectedFormulaValues);
            self::assertEquals(
                $expectedFormulaValues,
                $collectedFormulaValues,
                'Expected different formulas for modifier ' . $modifierValue
                . (count($missing = array_diff($expectedFormulaValues, $collectedFormulaValues)) > 0
                    ? ', missing: ' . implode(', ', $missing)
                    : ''
                )
                . (count($redundant = array_diff($collectedFormulaValues, $expectedFormulaValues)) > 0
                    ? ', not expecting: ' . implode(', ', $redundant)
                    : ''
                )
            );
        }
    }

    /**
     * @param string $modifierValue
     * @return array|string[]
     */
    private function getExpectedFormulaValues(string $modifierValue): array
    {
        $expectedFormulaValues = [];
        $formulasTable = new FormulasTable($this->createTablesShell(), $this->createModifiersTableShell());
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $modifierCodes = $formulasTable->getModifiers(FormulaCode::getIt($formulaValue));
            foreach ($modifierCodes as $modifierCode) {
                if ($modifierCode->getValue() === $modifierValue) {
                    $expectedFormulaValues[] = $formulaValue;
                    break;
                }
            }
        }

        return $expectedFormulaValues;
    }

    /**
     * Just a placeholder - intentionally no methods are expected to be called
     *
     * @return \Mockery\MockInterface|Tables
     */
    private function createTablesShell()
    {
        return $this->mockery(Tables::class);
    }

    /**
     * Just a placeholder - intentionally no methods are expected to be called
     *
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableShell()
    {
        return $this->mockery(ModifiersTable::class);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetFormulasFor
     * @expectedExceptionMessageRegExp ~black and white~
     */
    public function I_can_not_get_formulas_to_unknown_modifier()
    {
        (new ModifiersTable(Tables::getIt()))->getFormulas($this->createModifierCode('black and white'));
    }

    /**
     * @param string $value
     * @return \Mockery\MockInterface|ModifierCode
     */
    private function createModifierCode(string $value)
    {
        $modifierCode = $this->mockery(ModifierCode::class);
        $modifierCode->shouldReceive('getValue')
            ->andReturn($value);
        $modifierCode->shouldReceive('__toString')
            ->andReturn($value);

        return $modifierCode;
    }

    /**
     * @test
     */
    public function I_can_get_profiles_to_modifier()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $profileCodes = $modifiersTable->getProfiles(ModifierCode::getIt($modifierValue));
            self::assertTrue(is_array($profileCodes));
            self::assertNotEmpty($profileCodes);
            $collectedProfileValues = [];
            foreach ($profileCodes as $profileCode) {
                self::assertInstanceOf(ProfileCode::class, $profileCode);
                $collectedProfileValues[] = $profileCode->getValue();
            }
            sort($collectedProfileValues);
            $expectedProfileValues = $this->getExpectedProfileValues($modifierValue);
            sort($expectedProfileValues);
            self::assertEquals(
                $expectedProfileValues,
                $collectedProfileValues,
                "Expected different profiles for profile '{$modifierValue}'"
                . (count($redundant = array_diff($collectedProfileValues, $expectedProfileValues)) > 0
                    ? ', not expecting: ' . implode(', ', $redundant)
                    : ''
                )
                . (count($missing = array_diff($expectedProfileValues, $collectedProfileValues)) > 0
                    ? ', missing: ' . implode(', ', $missing)
                    : ''
                )
            );
        }
    }

    private static $impossibleProfiles = [
        ModifierCode::COLOR => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::GATE => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_MARS],
        ModifierCode::EXPLOSION => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::FILTER => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::WATCHER => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::THUNDER => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::INTERACTIVE_ILLUSION => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::HAMMER => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::CAMOUFLAGE => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::INVISIBILITY => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::MOVEMENT => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::BREACH => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::RECEPTOR => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::STEP_TO_FUTURE => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS,],
        ModifierCode::STEP_TO_PAST => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS,],
        ModifierCode::TRANSPOSITION => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::RELEASE => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::SCENT_MARS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_VENUS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
        ModifierCode::FRAGRANCE => [ProfileCode::BARRIER_VENUS, ProfileCode::BARRIER_MARS, ProfileCode::SPARK_VENUS, ProfileCode::SPARK_MARS, ProfileCode::RELEASE_VENUS, ProfileCode::RELEASE_MARS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::ILLUSION_MARS, ProfileCode::RECEPTOR_MARS, ProfileCode::BREACH_VENUS, ProfileCode::BREACH_MARS, ProfileCode::FIRE_VENUS, ProfileCode::FIRE_MARS, ProfileCode::GATE_VENUS, ProfileCode::GATE_MARS, ProfileCode::MOVEMENT_VENUS, ProfileCode::MOVEMENT_MARS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::TRANSPOSITION_MARS, ProfileCode::DISCHARGE_VENUS, ProfileCode::DISCHARGE_MARS, ProfileCode::WATCHER_VENUS, ProfileCode::WATCHER_MARS, ProfileCode::LOOK_VENUS, ProfileCode::LOOK_MARS, ProfileCode::TIME_VENUS, ProfileCode::TIME_MARS],
    ];

    /**
     * @param string $modifierValue
     * @return array|string[]
     */
    private function getExpectedProfileValues(string $modifierValue): array
    {
        $expectedProfileValues = array_diff(ProfileCode::getPossibleValues(), self::$impossibleProfiles[$modifierValue]);
        sort($expectedProfileValues);
        $profileValuesFromProfilesTable = $this->getProfileValuesFromProfilesTable($modifierValue);
        sort($profileValuesFromProfilesTable);
        self::assertSame($expectedProfileValues, $profileValuesFromProfilesTable);

        return $expectedProfileValues;
    }

    /**
     * @param string $modifierValue
     * @return array
     */
    private function getProfileValuesFromProfilesTable(string $modifierValue): array
    {
        $expectedProfileValues = [];
        $profilesTable = new ProfilesTable();
        foreach (ProfileCode::getPossibleValues() as $profileValue) {
            $modifierCodes = $profilesTable->getModifiersForProfile(ProfileCode::getIt($profileValue));
            foreach ($modifierCodes as $modifierCode) {
                if ($modifierCode->getValue() === $modifierValue) {
                    $expectedProfileValues[] = $this->reverseProfileGender($profileValue);
                    break;
                }
            }
        }

        return $expectedProfileValues;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetProfilesFor
     * @expectedExceptionMessageRegExp ~magnified~
     */
    public function I_can_not_get_profiles_to_unknown_modifiers()
    {
        (new ModifiersTable(Tables::getIt()))->getProfiles($this->createModifierCode('magnified'));
    }

    /**
     * @test
     */
    public function I_can_get_parent_modifiers()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        $fromParentMatchingProfiles = [];
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $profileValues = $this->getExpectedProfileValues($modifierValue);
            $fromParentToModifierMatchingProfiles = [];
            $modifier = ModifierCode::getIt($modifierValue);
            $parentModifiers = $modifiersTable->getParentModifiers($modifier);
            foreach ($parentModifiers as $parentModifier) {
                self::assertContains($modifier, $modifiersTable->getChildModifiers($parentModifier));
                $parentProfileValues = $this->getExpectedProfileValues($parentModifier->getValue());
                $matchingProfile = null;
                foreach ($parentProfileValues as $parentProfileValue) {
                    if (in_array($this->reverseProfileGender($parentProfileValue), $profileValues, true)) {
                        if ($matchingProfile !== null && $modifierValue !== ModifierCode::TRANSPOSITION) {
                            throw new \LogicException(
                                "For modifier '{$modifierValue}' has been already found matching parent profile '{$matchingProfile}'"
                                . " so can not use '{$parentProfileValue}'"
                            );
                        }
                        if ($parentProfileValue === ProfileCode::TRANSPOSITION_MARS) {
                            continue;
                        }
                        $matchingProfile = $parentProfileValue;
                    }
                }
                if ($matchingProfile === null) {
                    throw new \LogicException(
                        "No connecting profile has been found for modifier '{$modifierValue}' and its parent modifier '{$parentModifier}'"
                    );
                }
                if (array_key_exists($parentModifier->getValue(), $fromParentToModifierMatchingProfiles)) {
                    throw new \LogicException(
                        "Modifier '{$modifierValue}' is already connected with '{$parentModifier}' via profile "
                        . $fromParentToModifierMatchingProfiles[$parentModifier->getValue()] . "', so can not connect it also via '{$matchingProfile}'"
                    );
                }
                $fromParentToModifierMatchingProfiles[$parentModifier->getValue()] = $matchingProfile;
            }
            foreach ($fromParentToModifierMatchingProfiles as $fromParentToModifierMatchingProfile) {
                $fromParentMatchingProfiles[] = $fromParentToModifierMatchingProfile;
            }
        }
        foreach ($fromParentMatchingProfiles as $matchingProfile) {
            self::assertContains(
                '_venus',
                $matchingProfile,
                'Only venus profiles can be used on parent modifier side for connection (and mars on child side)'
            );
        }
    }

    /**
     * @test
     */
    public function I_can_get_child_modifiers()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        $fromChildrenMatchingProfiles = [];
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $profileValues = $this->getExpectedProfileValues($modifierValue);
            $fromChildToModifierMatchingProfiles = [];
            $modifier = ModifierCode::getIt($modifierValue);
            $childModifiers = $modifiersTable->getChildModifiers($modifier);
            foreach ($childModifiers as $childModifier) {
                self::assertContains($modifier, $modifiersTable->getParentModifiers($childModifier));
                $childProfileValues = $this->getExpectedProfileValues($childModifier->getValue());
                $matchingProfile = null;
                foreach ($childProfileValues as $childProfileValue) {
                    if (in_array($this->reverseProfileGender($childProfileValue), $profileValues, true)) {
                        if ($matchingProfile !== null && $modifierValue !== ModifierCode::TRANSPOSITION) {
                            throw new \LogicException(
                                "For modifier '{$modifierValue}' has been already found matching child profile '{$matchingProfile}'"
                                . " so can not use '{$childProfileValue}'"
                            );
                        }
                        if ($childProfileValue === ProfileCode::TRANSPOSITION_VENUS) {
                            continue;
                        }
                        $matchingProfile = $childProfileValue;
                    }
                }
                if ($matchingProfile === null) {
                    throw new \LogicException(
                        "No connecting profile has been found for modifier '{$modifierValue}' and its child modifier '{$childModifier}'"
                    );
                }
                if (array_key_exists($childModifier->getValue(), $fromChildToModifierMatchingProfiles)) {
                    throw new \LogicException(
                        "Modifier '{$modifierValue}' is already connected with '{$childModifier}' via profile "
                        . $fromChildToModifierMatchingProfiles[$childModifier->getValue()] . "', so can not connect it also via '{$matchingProfile}'"
                    );
                }
                $fromChildToModifierMatchingProfiles[$childModifier->getValue()] = $matchingProfile;
            }
            foreach ($fromChildToModifierMatchingProfiles as $fromChildToModifierMatchingProfile) {
                $fromChildrenMatchingProfiles[] = $fromChildToModifierMatchingProfile;
            }
        }
        foreach ($fromChildrenMatchingProfiles as $matchingProfile) {
            self::assertContains(
                '_mars',
                $matchingProfile,
                'Only mars profiles can be used by child modifiers to connect to parent (and venus on parent side)'
            );
        }
    }

    /**
     * @test
     */
    public function I_can_sum_difficulty_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        $singleModifier = ModifierCode::getIt(ModifierCode::INVISIBILITY);
        $singleModifierSum = $modifiersTable->getDifficultyChange($singleModifier)->getValue();
        self::assertNotEquals(0, $singleModifierSum);
        $difficultyChangeSum = $modifiersTable->sumDifficultyChanges([$singleModifier]);
        self::assertInstanceOf(IntegerInterface::class, $difficultyChangeSum);
        self::assertSame($singleModifierSum, $difficultyChangeSum->getValue());

        $flatArray = [
            ModifierCode::getIt(ModifierCode::STEP_TO_PAST),
            ModifierCode::getIt(ModifierCode::BREACH),
            ModifierCode::getIt(ModifierCode::CAMOUFLAGE),
            ModifierCode::getIt(ModifierCode::HAMMER),
        ];
        $flatArraySum = 0;
        foreach ($flatArray as $modifierCode) {
            $flatArraySum += $modifiersTable->getDifficultyChange($modifierCode)->getValue();
        }
        self::assertGreaterThan($singleModifierSum, $flatArraySum);
        self::assertSame($flatArraySum, $modifiersTable->sumDifficultyChanges($flatArray)->getValue());

        $treeArray = $flatArray;
        $treeArraySum = $flatArraySum;
        $treeArray[] = [ModifierCode::getIt(ModifierCode::COLOR), ModifierCode::getIt(ModifierCode::EXPLOSION)];
        $treeArraySum += $modifiersTable->getDifficultyChange(ModifierCode::getIt(ModifierCode::COLOR))->getValue();
        $treeArraySum += $modifiersTable->getDifficultyChange(ModifierCode::getIt(ModifierCode::EXPLOSION))->getValue();
        $treeArray[] = [ModifierCode::getIt(ModifierCode::RELEASE), [ModifierCode::getIt(ModifierCode::THUNDER)]];
        $treeArraySum += $modifiersTable->getDifficultyChange(ModifierCode::getIt(ModifierCode::RELEASE))->getValue();
        $treeArraySum += $modifiersTable->getDifficultyChange(ModifierCode::getIt(ModifierCode::THUNDER))->getValue();
        self::assertGreaterThan($flatArraySum, $treeArraySum);
        self::assertSame($treeArraySum, $modifiersTable->sumDifficultyChanges($treeArray)->getValue());
    }

    /**
     * @test
     */
    public function I_can_get_highest_required_realm()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(new Realm(0), $modifiersTable->getHighestRequiredRealm([]));

        self::assertEquals(new Realm(0), $modifiersTable->getHighestRequiredRealm([[]]));

        $singleModifier = ModifierCode::getIt(ModifierCode::INVISIBILITY);
        $singleModifierRealm = $modifiersTable->getRealm($singleModifier);
        self::assertEquals($singleModifierRealm, $modifiersTable->getHighestRequiredRealm([$singleModifier]));

        $flatArray = [
            ModifierCode::getIt(ModifierCode::STEP_TO_PAST),
            ModifierCode::getIt(ModifierCode::BREACH),
            ModifierCode::getIt(ModifierCode::CAMOUFLAGE),
            ModifierCode::getIt(ModifierCode::HAMMER),
        ];
        /** @var Realm|null $highestFlatRealm */
        $highestFlatRealm = null;
        $findHighestRealm = function ($modifierCodesOrCode) use (&$findHighestRealm, $modifiersTable) {
            if (!is_array($modifierCodesOrCode)) {
                return $modifiersTable->getRealm($modifierCodesOrCode);
            }
            $realms = [];
            foreach ($modifierCodesOrCode as $modifierCode) {
                if (is_array($modifierCode)) {
                    $highestRealm = $findHighestRealm($modifierCode);
                } else {
                    $highestRealm = $modifiersTable->getRealm($modifierCode);
                }
                $realms[$highestRealm->getValue()] = $highestRealm;
            }

            return $realms[max(array_keys($realms))];
        };
        $highestFlatRealm = $findHighestRealm($flatArray);
        self::assertEquals($highestFlatRealm, $modifiersTable->getHighestRequiredRealm($flatArray));

        $treeArray = $flatArray;
        $treeArray[] = [ModifierCode::getIt(ModifierCode::COLOR), ModifierCode::getIt(ModifierCode::EXPLOSION)];
        $treeArray[] = [ModifierCode::getIt(ModifierCode::RELEASE), [ModifierCode::getIt(ModifierCode::THUNDER)]];
        $highestTreeRealm = $findHighestRealm($treeArray);
        self::assertEquals($highestTreeRealm, $modifiersTable->getHighestRequiredRealm($treeArray));
    }

    /**
     * @test
     */
    public function I_can_get_summary_of_affections()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals([], $modifiersTable->getAffectionsOfModifiers([]));

        self::assertEquals([], $modifiersTable->getAffectionsOfModifiers([ModifierCode::getIt(ModifierCode::THUNDER) /* +0 */]));

        self::assertEquals(
            [
                AffectionPeriodCode::DAILY => new Affection([-2, AffectionPeriodCode::DAILY]),
                AffectionPeriodCode::LIFE => new Affection([-3, AffectionPeriodCode::LIFE]),
            ],
            $modifiersTable->getAffectionsOfModifiers([
                ModifierCode::getIt(ModifierCode::THUNDER), // 0
                [
                    [ModifierCode::getIt(ModifierCode::BREACH)], // -2
                    ModifierCode::getIt(ModifierCode::STEP_TO_PAST) // -3 live
                ],
            ])
        );

        self::assertEquals(
            [
                AffectionPeriodCode::DAILY => new Affection([-3, AffectionPeriodCode::DAILY]),
                AffectionPeriodCode::LIFE => new Affection([-4, AffectionPeriodCode::LIFE]),
            ],
            $modifiersTable->getAffectionsOfModifiers([
                ModifierCode::getIt(ModifierCode::THUNDER), // 0
                [
                    ModifierCode::getIt(ModifierCode::BREACH), // -2
                    ModifierCode::getIt(ModifierCode::GATE), // -1
                    ModifierCode::getIt(ModifierCode::STEP_TO_PAST), // -3 live
                    ModifierCode::getIt(ModifierCode::STEP_TO_FUTURE) // -1 live
                ],
            ])
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_modifiers_radius_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(new IntegerObject(0), $modifiersTable->sumRadiusChange([]));

        self::assertEquals(
            new IntegerObject(-6),
            $modifiersTable->sumRadiusChange(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // -6
                    ModifierCode::getIt(ModifierCode::BREACH), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // 0
                    ModifierCode::getIt(ModifierCode::TRANSPOSITION), // 0
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_modifiers_power_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(new IntegerObject(0), $modifiersTable->sumPowerChange([]));

        self::assertEquals(
            new IntegerObject(13),
            $modifiersTable->sumPowerChange(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // +6
                    ModifierCode::getIt(ModifierCode::FILTER), // null
                    ModifierCode::getIt(ModifierCode::THUNDER), // +10
                    ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // -3
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_detect_if_epicenter_has_been_shifted_by_modifiers()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertFalse($modifiersTable->isEpicenterShifted([]));

        self::assertFalse(
            $modifiersTable->isEpicenterShifted(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                    [
                        ModifierCode::getIt(ModifierCode::FILTER), // null
                        ModifierCode::getIt(ModifierCode::THUNDER), // null
                        ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                    ],
                ]
            )
        );

        self::assertTrue(
            $modifiersTable->isEpicenterShifted(
                [
                    [ModifierCode::getIt(ModifierCode::THUNDER), // null
                        [ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                            [ModifierCode::getIt(ModifierCode::FILTER), // null
                                [ModifierCode::getIt(ModifierCode::TRANSPOSITION), // 0
                                    [ModifierCode::getIt(ModifierCode::BREACH)], // null
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_modifiers_epicenter_shift_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumEpicenterShiftChange([])
        );

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumEpicenterShiftChange(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                    [
                        ModifierCode::getIt(ModifierCode::FILTER), // null
                        ModifierCode::getIt(ModifierCode::THUNDER), // null
                        ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                    ],
                ]
            )
        );

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumEpicenterShiftChange(
                [
                    [ModifierCode::getIt(ModifierCode::TRANSPOSITION), // 0
                        [ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                            [ModifierCode::getIt(ModifierCode::FILTER), // null
                                [ModifierCode::getIt(ModifierCode::TRANSPOSITION), // 0
                                    [ModifierCode::getIt(ModifierCode::TRANSPOSITION)], // 0
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_modifiers_speed_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumSpellSpeedChange([])
        );

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumSpellSpeedChange(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                    [
                        ModifierCode::getIt(ModifierCode::FILTER), // null
                        ModifierCode::getIt(ModifierCode::THUNDER), // null
                        ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                    ],
                ]
            )
        );

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumSpellSpeedChange(
                [
                    [ModifierCode::getIt(ModifierCode::TRANSPOSITION), // null
                        [ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                            [ModifierCode::getIt(ModifierCode::FILTER), // null
                                [ModifierCode::getIt(ModifierCode::MOVEMENT)], // 0
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_modifiers_attack_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());

        self::assertEquals(new IntegerObject(0), $modifiersTable->sumAttackChange([]));

        self::assertEquals(
            new IntegerObject(0),
            $modifiersTable->sumAttackChange(
                [
                    ModifierCode::getIt(ModifierCode::GATE), // null
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                    [
                        ModifierCode::getIt(ModifierCode::FILTER), // null
                        ModifierCode::getIt(ModifierCode::THUNDER), // null
                        ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                    ],
                ]
            )
        );

        self::assertEquals(
            new IntegerObject(8),
            $modifiersTable->sumAttackChange(
                [
                    [ModifierCode::getIt(ModifierCode::MOVEMENT), // null
                        [ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                            [ModifierCode::getIt(ModifierCode::FILTER), // null
                                [ModifierCode::getIt(ModifierCode::TRANSPOSITION)], // +4
                                [
                                    ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                                    ModifierCode::getIt(ModifierCode::TRANSPOSITION), // +4
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_casting_time_change()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        $timeTable = new TimeTable();
        $timeTable->getIndexedValues(); // just to populate values for sake of comparison

        self::assertEquals(new Casting(0, $timeTable), $modifiersTable->sumCastingChange([]));

        self::assertEquals(
            new Casting(0, $timeTable),
            $modifiersTable->sumCastingChange(
                [
                    ModifierCode::getIt(ModifierCode::EXPLOSION), // null
                    [
                        ModifierCode::getIt(ModifierCode::FILTER), // null
                        ModifierCode::getIt(ModifierCode::THUNDER), // null
                        ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION), // null
                    ],
                ]
            )
        );

        self::assertEquals(
            new Casting(8, $timeTable),
            $modifiersTable->sumCastingChange(
                [
                    [ModifierCode::getIt(ModifierCode::MOVEMENT), // null
                        [ModifierCode::getIt(ModifierCode::GATE), // +4
                            [ModifierCode::getIt(ModifierCode::FILTER), // null
                                [ModifierCode::getIt(ModifierCode::TRANSPOSITION)], // null
                                [
                                    ModifierCode::getIt(ModifierCode::GATE), // +4
                                    ModifierCode::getIt(ModifierCode::TRANSPOSITION), // null
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );
    }

}