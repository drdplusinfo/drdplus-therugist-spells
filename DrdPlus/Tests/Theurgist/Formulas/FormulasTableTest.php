<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\TraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use DrdPlus\Theurgist\Formulas\ModifiersTable;
use DrdPlus\Theurgist\Formulas\ProfilesTable;

class FormulasTableTest extends AbstractTheurgistTableTest
{
    /**
     * @var ModifiersTable
     */
    private $modifiersTable;

    protected function setUp()
    {
        $this->modifiersTable = new ModifiersTable();
    }

    /**
     * @test
     */
    public function I_can_get_every_obligatory_parameter()
    {
        $obligatoryParameters = ['realm', 'affection', 'casting', 'difficulty', 'duration'];
        foreach ($obligatoryParameters as $obligatoryParameter) {
            $this->I_can_get_obligatory_parameter($obligatoryParameter, FormulaCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_every_optional_parameter()
    {
        $optionalParameters = [
            'radius', 'power', 'attack', 'size_change', 'detail_level', 'brightness', 'speed', 'transposition'
        ];
        foreach ($optionalParameters as $optionalParameter) {
            $this->I_can_get_optional_parameter($optionalParameter, FormulaCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_forms()
    {
        $formulasTable = new FormulasTable();
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $forms = $formulasTable->getForms(FormulaCode::getIt($formulaValue));
            $formValues = [];
            foreach ($forms as $form) {
                self::assertInstanceOf(FormCode::class, $form);
                $formValues[] = $form->getValue();
            }
            self::assertSame($formValues, array_unique($formValues));
            sort($formValues);
            $expectedFormValues = $this->getExpectedFormValues($formulaValue);
            sort($expectedFormValues);
            self::assertEquals($expectedFormValues, $formValues, "Expected different forms for '{$formulaValue}'");
        }
    }

    private static $excludedFormValues = [
        FormulaCode::BARRIER => ['direct', 'volume', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::SMOKE => ['direct', 'planar', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::ILLUSION => ['direct', 'planar', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::METAMORPHOSIS => ['indirect', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        FormulaCode::FIRE => ['direct', 'planar', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::PORTAL => ['direct', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        FormulaCode::LIGHT => ['direct', 'planar', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::FLOW_OF_TIME => ['indirect', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => ['direct', 'planar', 'beam', 'tangible', 'visible', 'by_formula'],
        FormulaCode::HIT => ['direct', 'volume', 'planar', 'intangible', 'visible', 'by_formula'],
        FormulaCode::GREAT_MASSACRE => ['direct', 'planar', 'beam', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::DISCHARGE => ['direct', 'volume', 'planar', 'intangible', 'invisible', 'by_formula'],
        FormulaCode::LOCK => ['indirect', 'volume', 'planar', 'tangible', 'visible', 'by_formula'],
    ];

    private function getExpectedFormValues(string $formulaValue): array
    {
        $excludedFormValues = self::$excludedFormValues[$formulaValue];

        return array_diff(FormCode::getPossibleValues(), $excludedFormValues);
    }

    /**
     * @test
     */
    public function I_can_get_spell_traits()
    {
        $formulasTable = new FormulasTable();
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $spellTraits = $formulasTable->getSpellTraits(FormulaCode::getIt($formulaValue));
            /** @var array|string[] $expectedTraitValues */
            $expectedTraitValues = $this->getValueFromTable($formulasTable, $formulaValue, 'traits');
            $expectedSpellTraits = [];
            foreach ($expectedTraitValues as $expectedTraitValue) {
                $expectedSpellTraits[] = new SpellTrait($expectedTraitValue);
            }
            self::assertEquals($expectedSpellTraits, $spellTraits);

            $spellTraitCodeValues = [];
            foreach ($spellTraits as $spellTrait) {
                self::assertInstanceOf(SpellTrait::class, $spellTrait);
                $spellTraitCodeValues[] = $spellTrait->getTraitCode()->getValue();
            }
            self::assertSame($spellTraitCodeValues, array_unique($spellTraitCodeValues));
            sort($spellTraitCodeValues);
            $expectedTraitCodeValues = $this->getExpectedTraitCodeValues($formulaValue);
            sort($expectedTraitCodeValues);
            self::assertEquals($expectedTraitCodeValues, $spellTraitCodeValues, "Expected different traits for '{$formulaValue}'");
        }
    }

    private static $excludedTraitValues = [
        FormulaCode::BARRIER => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::SMOKE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::ILLUSION => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::METAMORPHOSIS => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::FIRE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::PORTAL => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::LIGHT => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::ACTIVE],
        FormulaCode::FLOW_OF_TIME => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::HIT => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::GREAT_MASSACRE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::DISCHARGE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        FormulaCode::LOCK => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT],
    ];

    private function getExpectedTraitCodeValues(string $formulaValue)
    {
        $excludedTraitValues = self::$excludedTraitValues[$formulaValue];

        return array_diff(TraitCode::getPossibleValues(), $excludedTraitValues);
    }

    /**
     * @test
     */
    public function I_can_get_modifiers_for_formula()
    {
        $formulasTable = new FormulasTable();
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $modifierCodes = $formulasTable->getModifiersForFormula(FormulaCode::getIt($formulaValue));
            self::assertTrue(is_array($modifierCodes));
            self::assertNotEmpty($modifierCodes);
            $collectedModifierValues = [];
            /** @var ModifierCode $modifierCode */
            foreach ($modifierCodes as $modifierCode) {
                self::assertInstanceOf(ModifierCode::class, $modifierCode);
                $collectedModifierValues[] = $modifierCode->getValue();
            }
            sort($collectedModifierValues);
            $possibleModifierValues = $this->getExpectedModifierValues($formulaValue);
            sort($possibleModifierValues);
            self::assertEquals(
                $possibleModifierValues,
                $collectedModifierValues,
                'Expected different modifiers for formula ' . $formulaValue
            );

            $matchingModifierValues = $this->getModifiersFromProfilesTable($formulaValue);
            sort($matchingModifierValues);
            self::assertEquals(
                $matchingModifierValues,
                $collectedModifierValues,
                'Expected different modifiers for formula ' . $formulaValue
            );
        }
    }

    private static $impossibleModifiers = [
        FormulaCode::BARRIER => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::SMOKE => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST],
        FormulaCode::ILLUSION => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::THUNDER, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        FormulaCode::METAMORPHOSIS => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::TRANSPOSITION, ModifierCode::FRAGRANCE],
        FormulaCode::FIRE => [ModifierCode::GATE, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::PORTAL => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        FormulaCode::LIGHT => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::FLOW_OF_TIME => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::HIT => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        FormulaCode::GREAT_MASSACRE => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::DISCHARGE => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::FRAGRANCE],
        FormulaCode::LOCK => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE, ModifierCode::STEP_TO_PAST, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
    ];

    /**
     * @param string $formulaValue
     * @return array|string[]
     */
    private function getExpectedModifierValues(string $formulaValue): array
    {
        $expectedModifierValues = array_diff(ModifierCode::getPossibleValues(), self::$impossibleModifiers[$formulaValue]);
        sort($expectedModifierValues);
        $modifierValuesFromModifiersTable = $this->getModifierValuesFromModifiersTable($formulaValue);
        sort($modifierValuesFromModifiersTable);
        self::assertSame($expectedModifierValues, $modifierValuesFromModifiersTable);

        return $expectedModifierValues;
    }

    /**
     * @param string $formulaValue
     * @return array
     */
    private function getModifierValuesFromModifiersTable(string $formulaValue): array
    {
        $modifierValues = [];
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $formulaCodes = $this->modifiersTable->getFormulasForModifier(ModifierCode::getIt($modifierValue));
            foreach ($formulaCodes as $formulaCode) {
                if ($formulaCode->getValue() === $formulaValue) {
                    $modifierValues[] = $modifierValue;
                    break;
                }
            }
        }

        return $modifierValues;
    }

    /**
     * @param string $formulaValue
     * @return array
     */
    private function getModifiersFromProfilesTable(string $formulaValue): array
    {
        $matchingProfileValues = $this->getProfilesByProfileTable($formulaValue);
        $t = new ProfilesTable();
        $matchingModifierValues = [];
        foreach ($matchingProfileValues as $matchingProfileValue) {
            foreach ($t->getModifiersForProfile(ProfileCode::getIt($matchingProfileValue)) as $modifierCode) {
                $matchingModifierValues[] = $modifierCode->getValue();
            }
        }

        return array_unique($matchingModifierValues);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetModifiersFor
     * @expectedExceptionMessageRegExp ~Abraka dabra~
     */
    public function I_can_not_get_modifiers_to_unknown_formula()
    {
        (new FormulasTable())->getModifiersForFormula($this->createFormulaCode('Abraka dabra'));
    }

    /**
     * @param string $value
     * @return \Mockery\MockInterface|FormulaCode
     */
    private function createFormulaCode(string $value)
    {
        $formulaCode = $this->mockery(FormulaCode::class);
        $formulaCode->shouldReceive('getValue')
            ->andReturn($value);
        $formulaCode->shouldReceive('__toString')
            ->andReturn($value);

        return $formulaCode;
    }

    /**
     * @test
     */
    public function I_can_get_profiles_for_formula()
    {
        $formulasTable = new FormulasTable();
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $profileCodes = $formulasTable->getProfilesForFormula(FormulaCode::getIt($formulaValue));
            self::assertTrue(is_array($profileCodes));
            self::assertNotEmpty($profileCodes);
            $profileValues = [];
            foreach ($profileCodes as $profileCode) {
                self::assertInstanceOf(ProfileCode::class, $profileCode);
                $profileValues[] = $profileCode->getValue();
            }
            sort($profileValues);
            $expectedProfiles = $this->getExpectedProfilesFor($formulaValue);
            sort($expectedProfiles);
            self::assertEquals(
                $expectedProfiles,
                $profileValues,
                "Expected different profiles for formula '{$formulaValue}'"
            );
            $profilesByProfileTable = $this->getProfilesByProfileTable($formulaValue);
            sort($profilesByProfileTable);
            self::assertEquals(
                $profilesByProfileTable,
                $profileValues,
                "Expected different profiles for formula '{$formulaValue}'"
            );
        }
    }

    private static $impossibleVenusProfiles = [
        FormulaCode::BARRIER => [ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::SMOKE => [ProfileCode::BARRIER_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::ILLUSION => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::METAMORPHOSIS => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::FIRE => [ProfileCode::BARRIER_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::PORTAL => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::LIGHT => [ProfileCode::BARRIER_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::FLOW_OF_TIME => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::HIT => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::GREAT_MASSACRE => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::DISCHARGE => [ProfileCode::BARRIER_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::BREACH_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::TRANSPOSITION_VENUS, ProfileCode::WATCHER_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
        FormulaCode::LOCK => [ProfileCode::BARRIER_VENUS, ProfileCode::SPARK_VENUS, ProfileCode::RELEASE_VENUS, ProfileCode::SCENT_VENUS, ProfileCode::ILLUSION_VENUS, ProfileCode::RECEPTOR_VENUS, ProfileCode::FIRE_VENUS, ProfileCode::GATE_VENUS, ProfileCode::MOVEMENT_VENUS, ProfileCode::DISCHARGE_VENUS, ProfileCode::LOOK_VENUS, ProfileCode::TIME_VENUS],
    ];

    /**
     * @param string $formulaValue
     * @return array|string[]
     */
    private function getExpectedProfilesFor(string $formulaValue): array
    {
        return array_diff(
            ProfileCode::getPossibleValues(),
            self::$impossibleVenusProfiles[$formulaValue],
            self::getMarsProfiles()
        );
    }

    /**
     * @return array|string[]
     */
    private static function getMarsProfiles(): array
    {
        return array_filter(
            ProfileCode::getPossibleValues(),
            function (string $profileValue) {
                return strpos($profileValue, '_mars') !== false;
            }
        );
    }

    /**
     * @param string $formulaValue
     * @return array|string[]
     */
    private function getProfilesByProfileTable(string $formulaValue): array
    {
        $profilesTable = new ProfilesTable();
        $matchingProfiles = [];
        foreach (ProfileCode::getPossibleValues() as $profileValue) {
            foreach ($profilesTable->getFormulasForProfile(ProfileCode::getIt($profileValue)) as $formulaCode) {
                if ($formulaCode->getValue() === $formulaValue) {
                    $oppositeProfile = $this->reverseProfileGender($profileValue);
                    $matchingProfiles[] = $oppositeProfile;
                    break;
                }
            }
        }

        return array_unique($matchingProfiles);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownFormulaToGetProfilesFor
     * @expectedExceptionMessageRegExp ~Charge!~
     */
    public function I_can_not_get_profiles_to_unknown_formula()
    {
        (new FormulasTable())->getProfilesForFormula($this->createFormulaCode('Charge!'));
    }

}