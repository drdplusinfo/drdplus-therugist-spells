<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedTable;
use DrdPlus\Theurgist\Codes\AffectionPeriodCode;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyLimit;
use DrdPlus\Theurgist\Formulas\CastingParameters\Realm;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use DrdPlus\Theurgist\Formulas\ModifiersTable;
use DrdPlus\Theurgist\Formulas\ProfilesTable;
use Granam\Integer\IntegerObject;

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
        /**
         * @see FormulasTable::getRealm()
         * @see FormulasTable::getAffection()
         * @see FormulasTable::getCasting()
         * @see FormulasTable::getDifficultyLimit()
         * @see FormulasTable::getDuration()
         */
        $obligatoryParameters = ['realm', 'affection', 'casting', 'difficulty_limit', 'duration'];
        foreach ($obligatoryParameters as $obligatoryParameter) {
            $this->I_can_get_obligatory_parameter($obligatoryParameter, FormulaCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_every_optional_parameter()
    {
        /**
         * @see FormulasTable::getRadius()
         * @see FormulasTable::getPower()
         * @see FormulasTable::getAttack()
         * @see FormulasTable::getSizeChange()
         * @see FormulasTable::getDetailLevel()
         * @see FormulasTable::getBrightness()
         * @see FormulasTable::getSpellSpeed()
         * @see FormulasTable::getEpicenterShift()
         */
        $optionalParameters = [
            'radius', 'power', 'attack', 'size_change', 'detail_level', 'brightness', 'spell_speed', 'epicenter_shift',
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

            $spellSpellTraitCodeValues = [];
            foreach ($spellTraits as $spellTrait) {
                self::assertInstanceOf(SpellTrait::class, $spellTrait);
                $spellSpellTraitCodeValues[] = $spellTrait->getSpellTraitCode()->getValue();
            }
            self::assertSame($spellSpellTraitCodeValues, array_unique($spellSpellTraitCodeValues));
            sort($spellSpellTraitCodeValues);
            $expectedSpellTraitCodeValues = $this->getExpectedSpellTraitCodeValues($formulaValue);
            sort($expectedSpellTraitCodeValues);
            self::assertEquals($expectedSpellTraitCodeValues, $spellSpellTraitCodeValues, "Expected different traits for '{$formulaValue}'");
        }
    }

    private static $excludedTraitValues = [
        FormulaCode::BARRIER => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::SMOKE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::ILLUSION => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::METAMORPHOSIS => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::FIRE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::PORTAL => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::LIGHT => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::ACTIVE],
        FormulaCode::FLOW_OF_TIME => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::HIT => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::GREAT_MASSACRE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::DISCHARGE => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT, SpellTraitCode::ACTIVE],
        FormulaCode::LOCK => [SpellTraitCode::AFFECTING, SpellTraitCode::INVISIBLE, SpellTraitCode::SILENT, SpellTraitCode::ODORLESS, SpellTraitCode::CYCLIC, SpellTraitCode::MEMORY, SpellTraitCode::DEFORMATION, SpellTraitCode::UNIDIRECTIONAL, SpellTraitCode::BIDIRECTIONAL, SpellTraitCode::INACRID, SpellTraitCode::EVERY_SENSE, SpellTraitCode::SITUATIONAL, SpellTraitCode::SHAPESHIFT, SpellTraitCode::STATE_CHANGE, SpellTraitCode::NATURE_CHANGE, SpellTraitCode::NO_SMOKE, SpellTraitCode::TRANSPARENCY, SpellTraitCode::MULTIPLE_ENTRY, SpellTraitCode::OMNIPRESENT],
    ];

    private function getExpectedSpellTraitCodeValues(string $formulaValue): array
    {
        $excludedTraitValues = self::$excludedTraitValues[$formulaValue];

        return array_diff(SpellTraitCode::getPossibleValues(), $excludedTraitValues);
    }

    /**
     * @test
     */
    public function I_can_get_modifiers_for_formula()
    {
        $formulasTable = new FormulasTable();
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $modifierCodes = $formulasTable->getModifiers(FormulaCode::getIt($formulaValue));
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
            $formulaCodes = $this->modifiersTable->getFormulas(ModifierCode::getIt($modifierValue));
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
        (new FormulasTable())->getModifiers($this->createFormulaCode('Abraka dabra'));
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
            $profileCodes = $formulasTable->getProfiles(FormulaCode::getIt($formulaValue));
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
        (new FormulasTable())->getProfiles($this->createFormulaCode('Charge!'));
    }

    /**
     * @test
     */
    public function I_can_get_difficulty_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $fire = FormulaCode::getIt(FormulaCode::FIRE);

        $modifiers = ['foo', 'bar'];
        $difficultyOfModifiedFormula = $formulasTable->getDifficultyOfModified(
            $fire,
            $modifiers,
            $this->createModifiersTableForDifficulty($modifiers, 123, 456)
        );
        self::assertSame(
            $formulasTable->getDifficultyLimit($fire)->getMinimal() + 123,
            $difficultyOfModifiedFormula->getValue()
        );
    }

    /**
     * @param array $expectedModifiers
     * @param int $difficultyChange
     * @param int $highestRequiredReam
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForDifficulty(array $expectedModifiers, int $difficultyChange, int $highestRequiredReam)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumDifficultyChanges')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($difficultyChange));
        $modifiersTable->shouldReceive('getHighestRequiredRealm')
            ->andReturn(new Realm($highestRequiredReam));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_minimal_required_realm_of_modified_formula()
    {
        $this->I_can_get_minimal_required_realm_of_non_modified_formula();

        $this->I_can_get_minimal_required_realm_of_slightly_modified_formula();

        $this->I_can_get_minimal_required_realm_of_moderate_modified_formula();

        $this->I_can_get_minimal_required_realm_of_heavily_modified_formula();
    }

    private function I_can_get_minimal_required_realm_of_non_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $formulaCode = FormulaCode::getIt(FormulaCode::PORTAL);
        $requiredRealmOfNonModified = $formulasTable->getRealmOfModified($formulaCode, [], new ModifiersTable());
        self::assertEquals($formulasTable->getRealm($formulaCode), $requiredRealmOfNonModified);
    }

    private function I_can_get_minimal_required_realm_of_slightly_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::BARRIER);
        $modifiers = ['foo', 'bar', 'baz'];
        $formulasTable = new FormulasTable();
        $requiredRealmOfSlightlyModifiedFormula = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty(
                $modifiers,
                $difficultyChangeValue = 6 /* 14 + 6 = 20, still can be handled by formula minimal realm */,
                1 // highest required realm by modifiers
            )
        );
        self::assertInstanceOf(Realm::class, $requiredRealmOfSlightlyModifiedFormula);
        self::assertSame($formulasTable->getRealm($formulaCode)->getValue(), $requiredRealmOfSlightlyModifiedFormula->getValue());

        $requiredRealmOnHighModifiersRequirement = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty(
                $modifiers,
                $difficultyChangeValue = 6 /* 14 + 6 = 20, still can be handled by formula minimal realm */,
                123 // highest required realm by modifiers
            )
        );
        self::assertInstanceOf(Realm::class, $requiredRealmOnHighModifiersRequirement);
        self::assertSame(123, $requiredRealmOnHighModifiersRequirement->getValue());
    }

    private function I_can_get_minimal_required_realm_of_moderate_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::ILLUSION);
        $modifiers = ['foo', 'bar', 'baz'];
        $formulasTable = new FormulasTable();
        $requiredRealmOfModerateModifiedFormula = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty(
                $modifiers,
                $difficultyChangeValue = 9 /* 2 + 9 = 11, can not be handled by formula minimal realm */,
                1
            )
        );
        self::assertInstanceOf(Realm::class, $requiredRealmOfModerateModifiedFormula);
        self::assertGreaterThan(
            $formulasTable->getRealm($formulaCode)->getValue(),
            $requiredRealmOfModerateModifiedFormula->getValue()
        );
        $basicFormulaRealmValue = $formulasTable->getRealm($formulaCode)->getValue();
        $portalDifficultyLimit = $formulasTable->getDifficultyLimit($formulaCode);
        $unhandledDifficulty = ($difficultyChangeValue + $portalDifficultyLimit->getMinimal()) - $portalDifficultyLimit->getMaximal();
        $handledDifficultyPerRealm = $portalDifficultyLimit->getAdditionByRealms()->getAddition();
        $realmsIncrement = (int)ceil($unhandledDifficulty / $handledDifficultyPerRealm);
        self::assertSame($basicFormulaRealmValue + $realmsIncrement, $requiredRealmOfModerateModifiedFormula->getValue());

        $requiredRealmOfHighModifiersRequirement = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty(
                $modifiers,
                $difficultyChangeValue = 9 /* 2 + 9 = 11, can not be handled by formula minimal realm */,
                456
            )
        );
        self::assertSame(456, $requiredRealmOfHighModifiersRequirement->getValue());
    }

    private function I_can_get_minimal_required_realm_of_heavily_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::DISCHARGE);
        $modifiers = ['foo', 'BAR'];
        $formulasTable = new FormulasTable();
        $requiredRealmOfHeavilyModifiedFormula = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty($modifiers, $difficultyChangeValue = 159, 1)
        );
        self::assertInstanceOf(Realm::class, $requiredRealmOfHeavilyModifiedFormula);
        self::assertGreaterThan(
            $formulasTable->getRealm($formulaCode)->getValue(),
            $requiredRealmOfHeavilyModifiedFormula->getValue()
        );
        $basicFormulaRealmValue = $formulasTable->getRealm($formulaCode)->getValue();
        $portalDifficultyLimit = $formulasTable->getDifficultyLimit($formulaCode);
        $unhandledDifficulty = ($difficultyChangeValue + $portalDifficultyLimit->getMinimal()) - $portalDifficultyLimit->getMaximal();
        $handledDifficultyPerRealm = $portalDifficultyLimit->getAdditionByRealms()->getAddition();
        $realmsIncrement = (int)ceil($unhandledDifficulty / $handledDifficultyPerRealm);
        self::assertSame(
            $basicFormulaRealmValue + $realmsIncrement,
            $requiredRealmOfHeavilyModifiedFormula->getValue()
        );
        $requiredRealmOfHighModifiersRequirement = $formulasTable->getRealmOfModified(
            $formulaCode,
            $modifiers,
            $this->createModifiersTableForDifficulty($modifiers, $difficultyChangeValue = 159, $requiredRealmOfHeavilyModifiedFormula->getValue() + 1)
        );
        self::assertSame($requiredRealmOfHeavilyModifiedFormula->getValue() + 1, $requiredRealmOfHighModifiersRequirement->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_get_minimal_required_realm_of_heavily_modified_formula_with_negative_addition()
    {
        $formulasTable = $this->mockery(FormulasTable::class);
        $formulasTable->shouldReceive('getDifficultyLimit')
            ->andReturn(new DifficultyLimit([123, 456, -1 /* higher realm cause lower difficulty to be handled */]));
        $getDataFileName = new \ReflectionMethod(FormulasTable::class, 'getDataFileName');
        $getDataFileName->setAccessible(true);
        $formulasTable->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getDataFileName')
            ->andReturn($getDataFileName->invoke(new FormulasTable()));
        $formulasTable = $formulasTable->makePartial(); // call original methods

        $modifiers = ['foo'];
        /** @var FormulasTable $formulasTable */
        try {
            $formulasTable->getRealmOfModified(
                FormulaCode::getIt(FormulaCode::FLOW_OF_TIME),
                $modifiers,
                $this->createModifiersTableForDifficulty($modifiers, $difficultyChangeValue = 333, 1)
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }

        $formulasTable->getRealmOfModified(
            FormulaCode::getIt(FormulaCode::FLOW_OF_TIME),
            $modifiers,
            $this->createModifiersTableForDifficulty($modifiers, $difficultyChangeValue = 334, 1)
        );
    }

    /**
     * @test
     */
    public function I_can_get_affections_of_modified_formula()
    {
        $formulasTable = new FormulasTable();

        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-1, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(
                FormulaCode::getIt(FormulaCode::DISCHARGE) /* -1 */,
                [],
                $this->modifiersTable
            )
        );

        $expectedModifiers = ['foo', 'bar'];
        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-1, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(
                FormulaCode::getIt(FormulaCode::FLOW_OF_TIME),
                $expectedModifiers,
                $this->createModifiersTableForAffections(
                    $expectedModifiers,
                    [
                        [0, AffectionPeriodCode::DAILY],
                    ]
                )
            )
        );

        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-84, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(
                FormulaCode::getIt(FormulaCode::FLOW_OF_TIME),
                $expectedModifiers,
                $this->createModifiersTableForAffections(
                    $expectedModifiers,
                    [
                        [-1, AffectionPeriodCode::DAILY],
                        [-4, AffectionPeriodCode::DAILY],
                        [-78, AffectionPeriodCode::DAILY],
                    ]
                )
            )
        );

        self::assertEquals(
            [
                AffectionPeriodCode::DAILY => new Affection([-85, AffectionPeriodCode::DAILY]),
                AffectionPeriodCode::LIFE => new Affection([-516, AffectionPeriodCode::LIFE]),
            ],
            $formulasTable->getAffectionsOfModified(
                FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // -2
                $expectedModifiers,
                $this->createModifiersTableForAffections(
                    $expectedModifiers,
                    [
                        [-1, AffectionPeriodCode::DAILY],
                        [-4, AffectionPeriodCode::DAILY],
                        [-78, AffectionPeriodCode::DAILY],
                        [-159, AffectionPeriodCode::LIFE],
                        [-357, AffectionPeriodCode::LIFE],
                    ]
                )
            )
        );
    }

    /**
     * @param array $expectedModifiers
     * @param array $affectionsOfModifiers
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForAffections(array $expectedModifiers, array $affectionsOfModifiers)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('getAffectionsOfModifiers')
            ->with($expectedModifiers)
            ->andReturn(
                array_map(function (array $affectionParts) {
                    return new Affection($affectionParts);
                }, $affectionsOfModifiers)
            );

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_radius_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $distanceTable = new DistanceTable();
        $modifiers = ['foo', 'bar'];

        $radiusOfLock = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no radius (null)
            $modifiers,
            $this->createModifiersTableForRadius($modifiers, $distanceTable, 132456),
            $distanceTable
        );
        self::assertNull($radiusOfLock);

        $radiusOfDischargeWithoutChange = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +10
            $modifiers,
            $this->createModifiersTableForRadius($modifiers, $distanceTable, 0),
            $distanceTable
        );
        self::assertEquals(new DistanceBonus(10, $distanceTable), $radiusOfDischargeWithoutChange);

        $radiusOfModifiedDischarge = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +10
            $modifiers,
            $this->createModifiersTableForRadius($modifiers, $distanceTable, 789),
            $distanceTable
        );
        self::assertEquals(new DistanceBonus(799, $distanceTable), $radiusOfModifiedDischarge);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfRadii
     * @param DistanceTable $expectedDistanceTable
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForRadius(
        array $expectedModifiers,
        DistanceTable $expectedDistanceTable,
        int $sumOfRadii
    )
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumRadiusChange')
            ->with($expectedModifiers, $expectedDistanceTable)
            ->andReturn(new IntegerObject($sumOfRadii));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_power_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $modifiers = ['foo', 'bar'];

        $powerOfDischarge = $formulasTable->getPowerOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // null
            $modifiers,
            $this->createModifiersTableForPower($modifiers, 132456)
        );
        self::assertNull($powerOfDischarge);

        $powerOfLockWithoutChange = $formulasTable->getPowerOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // 0
            $modifiers,
            $this->createModifiersTableForPower($modifiers, 0)
        );
        self::assertEquals(new IntegerObject(0), $powerOfLockWithoutChange);

        $powerOfGreatMassacreWithChange = $formulasTable->getPowerOfModified(
            FormulaCode::getIt(FormulaCode::GREAT_MASSACRE), // 6
            $modifiers,
            $this->createModifiersTableForPower($modifiers, 789)
        );
        self::assertEquals(new IntegerObject(795), $powerOfGreatMassacreWithChange);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfPower
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForPower(array $expectedModifiers, int $sumOfPower)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumPowerChange')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($sumOfPower));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_epicenter_shift_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $distanceTable = new DistanceTable();
        $modifiers = ['foo', 'bar'];

        $epicenterShiftOfNotShiftedLock = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no epicenter shift (null)
            $modifiers,
            $this->createModifiersTableForEpicenterShift($modifiers, $distanceTable, false /* not shifted */, 123456),
            $distanceTable
        );
        self::assertNull($epicenterShiftOfNotShiftedLock);

        $epicenterShiftOfShiftedLock = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no epicenter shift (null)
            $modifiers,
            $this->createModifiersTableForEpicenterShift($modifiers, $distanceTable, true /* shifted */, 123456),
            $distanceTable
        );
        self::assertEquals(new DistanceBonus(123456, $distanceTable), $epicenterShiftOfShiftedLock);

        $epicenterShiftOfGreatMassacreWithoutChange = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::GREAT_MASSACRE), // +20
            $modifiers,
            $this->createModifiersTableForEpicenterShift($modifiers, $distanceTable, true /* shifted */, 0),
            $distanceTable
        );
        self::assertEquals(new DistanceBonus(20, $distanceTable), $epicenterShiftOfGreatMassacreWithoutChange);

        $epicenterShiftOfModifiedGreatMassacre = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::GREAT_MASSACRE), // +20
            $modifiers,
            $this->createModifiersTableForEpicenterShift($modifiers, $distanceTable, true /* shifted */, 789),
            $distanceTable
        );
        self::assertEquals(new DistanceBonus(809, $distanceTable), $epicenterShiftOfModifiedGreatMassacre);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfShifts
     * @param DistanceTable $expectedDistanceTable
     * @param bool $epicenterShifted
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForEpicenterShift(
        array $expectedModifiers,
        DistanceTable $expectedDistanceTable,
        bool $epicenterShifted,
        int $sumOfShifts
    )
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumEpicenterShiftChange')
            ->with($expectedModifiers, $expectedDistanceTable)
            ->andReturn(new IntegerObject($sumOfShifts));
        $modifiersTable->shouldReceive('isEpicenterShifted')
            ->andReturn($epicenterShifted);

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_spell_speed_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $speedTable = new SpeedTable();
        $modifiers = ['foo', 'bar'];

        $spellSpeedOfLock = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no spell speed (null)
            $modifiers,
            $this->createModifiersTableForSpellSpeed($modifiers, $speedTable, 132456),
            $speedTable
        );
        self::assertNull($spellSpeedOfLock);

        $spellSpeedOfTsunamiWithoutChange = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // +0
            $modifiers,
            $this->createModifiersTableForSpellSpeed($modifiers, $speedTable, 0),
            $speedTable
        );
        self::assertEquals(new SpeedBonus(0, $speedTable), $spellSpeedOfTsunamiWithoutChange);

        $spellSpeedOfModifiedTsunami = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // +0
            $modifiers,
            $this->createModifiersTableForSpellSpeed($modifiers, $speedTable, 789),
            $speedTable
        );
        self::assertEquals(new SpeedBonus(789, $speedTable), $spellSpeedOfModifiedTsunami);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfSpeedChange
     * @param SpeedTable $expectedSpeedTable
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForSpellSpeed(
        array $expectedModifiers,
        SpeedTable $expectedSpeedTable,
        int $sumOfSpeedChange
    )
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumSpellSpeedChange')
            ->with($expectedModifiers, $expectedSpeedTable)
            ->andReturn(new IntegerObject($sumOfSpeedChange));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_attack_of_modified_formula()
    {
        $formulasTable = new FormulasTable();
        $modifiers = ['foo', 'bar'];

        $attackOfLock = $formulasTable->getAttackOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no spell speed (null)
            $modifiers,
            $this->createModifiersTableForAttack($modifiers, 132456)
        );
        self::assertNull($attackOfLock);

        $attackOfDischargeWithoutChange = $formulasTable->getAttackOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +4
            $modifiers,
            $this->createModifiersTableForAttack($modifiers, 0)
        );
        self::assertEquals(new IntegerObject(4), $attackOfDischargeWithoutChange);

        $attackOfModifiedDischarge = $formulasTable->getAttackOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +0
            $modifiers,
            $this->createModifiersTableForAttack($modifiers, 789)
        );
        self::assertEquals(new IntegerObject(793), $attackOfModifiedDischarge);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfSpeedChange
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForAttack(array $expectedModifiers, int $sumOfSpeedChange)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumAttackChange')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($sumOfSpeedChange));

        return $modifiersTable;
    }

}