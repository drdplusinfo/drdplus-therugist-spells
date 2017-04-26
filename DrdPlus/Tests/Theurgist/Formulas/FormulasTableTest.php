<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Codes\TimeUnitCode;
use DrdPlus\Tables\Measurements\Distance\DistanceBonus;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use DrdPlus\Tables\Measurements\Speed\SpeedBonus;
use DrdPlus\Tables\Measurements\Speed\SpeedTable;
use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\AffectionPeriodCode;
use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Affection;
use DrdPlus\Theurgist\Formulas\CastingParameters\CastingRounds;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyChange;
use DrdPlus\Theurgist\Formulas\CastingParameters\DifficultyLimit;
use DrdPlus\Theurgist\Formulas\CastingParameters\Realm;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTraitsTable;
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
    /**
     * @var SpellTraitsTable
     */
    private $spellTraitsTable;

    protected function setUp()
    {
        $this->modifiersTable = new ModifiersTable(Tables::getIt());
        $this->spellTraitsTable = new SpellTraitsTable();
    }

    /**
     * @test
     */
    public function I_can_get_every_mandatory_parameter()
    {
        /**
         * @see FormulasTable::getRealm()
         * @see FormulasTable::getAffection()
         * @see FormulasTable::getEvocation()
         * @see FormulasTable::getDifficultyLimit()
         * @see FormulasTable::getDuration()
         */
        $mandatoryParameters = ['realm', 'affection', 'evocation', 'difficulty_limit', 'duration'];
        foreach ($mandatoryParameters as $mandatoryParameter) {
            $this->I_can_get_mandatory_parameter($mandatoryParameter, FormulaCode::class);
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
    public function Every_getter_of_modified_formula_parameter_has_same_interface()
    {
        foreach (get_class_methods(FormulasTable::class) as $methodName) {
            if (strpos($methodName, 'Modified') === false) {
                continue;
            }
            $methodReflection = new \ReflectionMethod(FormulasTable::class, $methodName);
            $parameters = $methodReflection->getParameters();
            self::assertCount(
                3,
                $parameters,
                "Expected exactly three parameters for '{$methodName}'"
                . ', formula code, array of modifier codes and array of spell trait codes'
            );
            self::assertNotNull(
                $parameters[0]->getClass(),
                "First parameter of '{$methodName}' should be of type formula code class"
            );
            self::assertSame(FormulaCode::class, $parameters[0]->getClass()->getName());
            self::assertSame('formulaCode', $parameters[0]->getName());
            self::assertTrue(
                $parameters[1]->isArray(),
                "Expected array of modifier codes as a second parameter of '{$methodName}', got " . $parameters[1]->getType()
            );
            self::assertSame('modifierCodes', $parameters[1]->getName());
            self::assertTrue(
                $parameters[2]->isArray(),
                "Expected array of spell trait codes as a third parameter of '{$methodName}', got " . $parameters[2]->getType()
            );
            self::assertSame('spellTraitCodes', $parameters[2]->getName());
            self::assertContains(<<<PHPDOC
* @param FormulaCode \$formulaCode
* @param array|ModifierCode[] \$modifierCodes
* @param array|SpellTraitCode[] \$spellTraitCodes
PHPDOC
                , preg_replace('~ {2,}~', '', $methodReflection->getDocComment()),
                "Expected different PHPDoc for '{$methodName}'"
            );
        }
    }

    /**
     * @test
     */
    public function I_can_get_casting()
    {
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $casting = $formulasTable->getCasting(FormulaCode::getIt($formulaValue));
            self::assertEquals(new Time(1, TimeUnitCode::ROUND, Tables::getIt()->getTimeTable()), $casting);
            self::assertSame(
                1,
                $casting->getInUnit(TimeUnitCode::ROUND)->getValue(),
                'Expected single round as casting time for any non-modified formula'
            );
        }
    }

    /**
     * @test
     */
    public function I_can_get_forms()
    {
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
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
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
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
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
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
        (new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable))
            ->getModifiers($this->createFormulaCode('Abraka dabra'));
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
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
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
        (new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable))->getProfiles($this->createFormulaCode('Charge!'));
    }

    /**
     * @test
     */
    public function I_can_get_difficulty_of_modified_formula()
    {
        $modifiers = ['foo', 'bar'];
        $spellTraits = ['baz', 'qux'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty($modifiers, 123, 456),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 789)
        );
        $fire = FormulaCode::getIt(FormulaCode::FIRE);

        $difficultyOfModifiedFormula = $formulasTable->getDifficultyOfModified($fire, $modifiers, $spellTraits);
        self::assertSame(
            $formulasTable->getDifficultyLimit($fire)->getMinimal() + 123 + 789,
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
            ->andReturn(new DifficultyChange($difficultyChange));
        $modifiersTable->shouldReceive('getHighestRequiredRealm')
            ->andReturn(new Realm($highestRequiredReam));

        return $modifiersTable;
    }

    /**
     * @param array $expectedSpellTraits
     * @param int $difficultyChange
     * @return \Mockery\MockInterface|SpellTraitsTable
     */
    private function createSpellTraitsTableForDifficulty(array $expectedSpellTraits, int $difficultyChange)
    {
        $spellTraitsTable = $this->mockery(SpellTraitsTable::class);
        $spellTraitsTable->shouldReceive('sumDifficultyChanges')
            ->with($expectedSpellTraits)
            ->andReturn(new DifficultyChange($difficultyChange));

        return $spellTraitsTable;
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
        $formulasTable = new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable);
        $formulaCode = FormulaCode::getIt(FormulaCode::PORTAL);
        $requiredRealmOfNonModified = $formulasTable->getRealmOfModified($formulaCode, [], []);
        self::assertEquals($formulasTable->getRealm($formulaCode), $requiredRealmOfNonModified);
    }

    private function I_can_get_minimal_required_realm_of_slightly_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::BARRIER);
        $modifiers = ['foo', 'bar', 'baz'];
        $spellTraits = ['qux', 'FOO'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty(
                $modifiers,
                5 /* 3 + 5 = 8, still can be handled by barrier realm 1 */,
                1 // highest required realm by modifiers
            ),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 2 /* 8 + 2 = 10, still can be handled by barrier minimal realm 1 */)
        );
        $requiredRealmOfSlightlyModifiedFormula = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertEquals($formulasTable->getRealm($formulaCode), $requiredRealmOfSlightlyModifiedFormula);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty(
                $modifiers,
                5 /* 3 + 5 = 8, still can be handled by barrier realm 1 */,
                123 // highest required realm by modifiers
            ),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 2 /* 8 + 2 = 10, still can be handled by barrier minimal realm 1 */)
        );
        $requiredRealmOnHighModifiersRequirement = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertInstanceOf(Realm::class, $requiredRealmOnHighModifiersRequirement);
        self::assertSame(123, $requiredRealmOnHighModifiersRequirement->getValue());
    }

    private function I_can_get_minimal_required_realm_of_moderate_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::ILLUSION);
        $modifiers = ['foo', 'bar', 'baz'];
        $spellTraits = ['qux', 'FooBarBaz'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty(
                $modifiers,
                $modifiersDifficultyChangeValue = 6 /* 2 + 6 = 8, still can be handled by illusion minimal realm 1 */,
                1
            ),
            $this->createSpellTraitsTableForDifficulty(
                $spellTraits,
                $spellTraitsDifficultyChangeValue = 3 /* 8 + 3 = 11, can be handled by illusion realm 2 */
            )
        );
        $requiredRealmOfModerateModifiedFormula = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertInstanceOf(Realm::class, $requiredRealmOfModerateModifiedFormula);
        self::assertGreaterThan(
            $formulasTable->getRealm($formulaCode)->getValue(),
            $requiredRealmOfModerateModifiedFormula->getValue()
        );
        $basicFormulaRealmValue = $formulasTable->getRealm($formulaCode)->getValue();
        $portalDifficultyLimit = $formulasTable->getDifficultyLimit($formulaCode);
        $unhandledDifficulty = ($modifiersDifficultyChangeValue + $spellTraitsDifficultyChangeValue + $portalDifficultyLimit->getMinimal())
            - $portalDifficultyLimit->getMaximal();
        $handledDifficultyPerRealm = $portalDifficultyLimit->getAdditionByRealms()->getAddition();
        $realmsIncrement = (int)ceil($unhandledDifficulty / $handledDifficultyPerRealm);
        self::assertSame($basicFormulaRealmValue + $realmsIncrement, $requiredRealmOfModerateModifiedFormula->getValue());

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty(
                $modifiers,
                9, /* 2 + 9 = 11, can be handled by illusion realm 2 */
                456
            ),
            $this->createSpellTraitsTableForDifficulty(
                $spellTraits,
                15 /* 11 + 15 = 26, can be handled by illusion realm 8 */
            )
        );
        $requiredRealmOfHighModifiersRequirement = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertSame(456, $requiredRealmOfHighModifiersRequirement->getValue());
    }

    private function I_can_get_minimal_required_realm_of_heavily_modified_formula()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::DISCHARGE);
        $modifiers = ['foo', 'BAR'];
        $spellTraits = ['bAz', 'QUX'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty($modifiers, $modifiersDifficultyChangeValue = 159, 1),
            $this->createSpellTraitsTableForDifficulty($spellTraits, $spellTraitsDifficultyChangeValue = 234)
        );
        $requiredRealmOfHeavilyModifiedFormula = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertInstanceOf(Realm::class, $requiredRealmOfHeavilyModifiedFormula);
        self::assertGreaterThan(
            $formulasTable->getRealm($formulaCode)->getValue(),
            $requiredRealmOfHeavilyModifiedFormula->getValue()
        );
        $basicFormulaRealmValue = $formulasTable->getRealm($formulaCode)->getValue();
        $portalDifficultyLimit = $formulasTable->getDifficultyLimit($formulaCode);
        $unhandledDifficulty = ($modifiersDifficultyChangeValue + $spellTraitsDifficultyChangeValue + $portalDifficultyLimit->getMinimal())
            - $portalDifficultyLimit->getMaximal();
        $handledDifficultyPerRealm = $portalDifficultyLimit->getAdditionByRealms()->getAddition();
        $realmsIncrement = (int)ceil($unhandledDifficulty / $handledDifficultyPerRealm);
        self::assertSame(
            $basicFormulaRealmValue + $realmsIncrement,
            $requiredRealmOfHeavilyModifiedFormula->getValue()
        );

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForDifficulty($modifiers, 159, $requiredRealmOfHeavilyModifiedFormula->getValue() + 1),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 234)
        );
        $requiredRealmOfHighModifiersRequirement = $formulasTable->getRealmOfModified($formulaCode, $modifiers, $spellTraits);
        self::assertSame($requiredRealmOfHeavilyModifiedFormula->getValue() + 1, $requiredRealmOfHighModifiersRequirement->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\CanNotBuildFormulaWithRequiredModification
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_get_minimal_required_realm_of_heavily_modified_formula_with_negative_addition()
    {
        $modifiers = ['foo'];
        $spellTraits = ['bar'];
        $formulasTable = $this->getFormulasTableForMinimalRequiredRealmTest(
            $this->createModifiersTableForDifficulty($modifiers, 333, 1),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 0)
        );

        /** @var \Mockery\MockInterface|FormulasTable $formulasTable */
        try {
            $formulasTable->getRealmOfModified(FormulaCode::getIt(FormulaCode::FLOW_OF_TIME), $modifiers, $spellTraits);
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }

        $formulasTable = $this->getFormulasTableForMinimalRequiredRealmTest(
            $this->createModifiersTableForDifficulty($modifiers, 333, 1),
            $this->createSpellTraitsTableForDifficulty($spellTraits, 1 /* ++ */)
        );
        $formulasTable->getRealmOfModified(FormulaCode::getIt(FormulaCode::FLOW_OF_TIME), $modifiers, $spellTraits);
    }

    /**
     * @param ModifiersTable $modifiersTable
     * @param SpellTraitsTable $spellTraitsTable
     * @return \Mockery\Mock|\Mockery\MockInterface|FormulasTable
     */
    private function getFormulasTableForMinimalRequiredRealmTest(ModifiersTable $modifiersTable, SpellTraitsTable $spellTraitsTable)
    {
        $formulasTable = \Mockery::mock(FormulasTable::class, [Tables::getIt(), $modifiersTable, $spellTraitsTable]);
        $formulasTable->shouldReceive('getDifficultyLimit')
            ->andReturn(new DifficultyLimit([123, 456, -1 /* higher realm cause lower difficulty to be handled */]));
        $getDataFileName = new \ReflectionMethod(FormulasTable::class, 'getDataFileName');
        $getDataFileName->setAccessible(true);
        $formulasTable->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getDataFileName')
            ->andReturn($getDataFileName->invoke(new FormulasTable(Tables::getIt(), $this->modifiersTable, $this->spellTraitsTable)));
        $formulasTable = $formulasTable->makePartial(); // call original methods

        return $formulasTable;
    }

    /**
     * @test
     */
    public function I_can_get_affections_of_modified_formula()
    {
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAffections([], [[0, AffectionPeriodCode::DAILY]]),
            $this->createSpellTraitsTableShell()
        );
        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-1, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(FormulaCode::getIt(FormulaCode::DISCHARGE) /* -1 */, [], [])
        );

        $expectedModifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAffections($expectedModifiers, [[0, AffectionPeriodCode::DAILY]]),
            $this->createSpellTraitsTableShell()
        );
        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-1, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(FormulaCode::getIt(FormulaCode::FLOW_OF_TIME), $expectedModifiers, [])
        );

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAffections(
                $expectedModifiers,
                [
                    [-1, AffectionPeriodCode::DAILY],
                    [-4, AffectionPeriodCode::DAILY],
                    [-78, AffectionPeriodCode::DAILY],
                ]
            ),
            $this->createSpellTraitsTableShell()
        );
        self::assertEquals(
            [AffectionPeriodCode::DAILY => new Affection([-84, AffectionPeriodCode::DAILY])],
            $formulasTable->getAffectionsOfModified(FormulaCode::getIt(FormulaCode::FLOW_OF_TIME), $expectedModifiers, [])
        );

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAffections(
                $expectedModifiers,
                [
                    [-1, AffectionPeriodCode::DAILY],
                    [-4, AffectionPeriodCode::DAILY],
                    [-78, AffectionPeriodCode::DAILY],
                    [-159, AffectionPeriodCode::LIFE],
                    [-357, AffectionPeriodCode::LIFE],
                ]
            ),
            $this->createSpellTraitsTableShell()
        );
        self::assertEquals(
            [
                AffectionPeriodCode::DAILY => new Affection([-85, AffectionPeriodCode::DAILY]),
                AffectionPeriodCode::LIFE => new Affection([-516, AffectionPeriodCode::LIFE]),
            ],
            $formulasTable->getAffectionsOfModified(
                FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // -2
                $expectedModifiers,
                []
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
        $modifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForRadius($modifiers, 132456),
            $this->createSpellTraitsTableShell()
        );
        $radiusOfLock = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no radius (null)
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertNull($radiusOfLock);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForRadius($modifiers, 0),
            $this->createSpellTraitsTableShell()
        );
        $radiusOfDischargeWithoutChange = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +10
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        $distanceTable = new DistanceTable();
        $distanceTable->getIndexedValues(); // just to populate them for sake of comparison
        self::assertEquals(new DistanceBonus(10, $distanceTable), $radiusOfDischargeWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForRadius($modifiers, 789),
            $this->createSpellTraitsTableShell()
        );
        $radiusOfModifiedDischarge = $formulasTable->getRadiusOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // +10
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertEquals(new DistanceBonus(799, $distanceTable), $radiusOfModifiedDischarge);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfRadii
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForRadius(array $expectedModifiers, int $sumOfRadii)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumRadiusChange')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($sumOfRadii));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_power_of_modified_formula()
    {
        $modifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForPower($modifiers, 132456),
            $this->createSpellTraitsTableShell()
        );
        $powerOfDischarge = $formulasTable->getPowerOfModified(
            FormulaCode::getIt(FormulaCode::DISCHARGE), // null
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertNull($powerOfDischarge);

        $lock = FormulaCode::getIt(FormulaCode::LOCK);
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForPower($modifiers, 0),
            $this->createSpellTraitsTableShell()
        );
        $powerOfLockWithoutChange = $formulasTable->getPowerOfModified($lock, $modifiers, ['foo bar', 'bar BAZ']);
        self::assertEquals($formulasTable->getPower($lock), $powerOfLockWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForPower($modifiers, 789),
            $this->createSpellTraitsTableShell()
        );
        $greatMassacre = FormulaCode::getIt(FormulaCode::GREAT_MASSACRE);
        $powerOfGreatMassacreWithChange = $formulasTable->getPowerOfModified($greatMassacre, $modifiers, ['foo bar', 'bar BAZ']);
        self::assertEquals($formulasTable->getPower($greatMassacre)->add(789), $powerOfGreatMassacreWithChange);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfPower
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForPower(array $expectedModifiers, int $sumOfPower)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumPowerChanges')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($sumOfPower));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_epicenter_shift_of_modified_formula()
    {
        $modifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForEpicenterShift($modifiers, false /* not shifted */, 123456),
            $this->createSpellTraitsTableShell()
        );
        $epicenterShiftOfNotShiftedLock = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no epicenter shift (null)
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertNull($epicenterShiftOfNotShiftedLock);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForEpicenterShift($modifiers, true /* shifted */, 123456),
            $this->createSpellTraitsTableShell()
        );
        $epicenterShiftOfShiftedLock = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no epicenter shift (null)
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        $distanceTable = new DistanceTable();
        $distanceTable->getIndexedValues(); // just to populate them for sake of comparison
        self::assertEquals(new DistanceBonus(123456, $distanceTable), $epicenterShiftOfShiftedLock);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForEpicenterShift($modifiers, true /* shifted */, 0),
            $this->createSpellTraitsTableShell()
        );
        $epicenterShiftOfGreatMassacreWithoutChange = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::GREAT_MASSACRE), // +20
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertEquals(new DistanceBonus(20, $distanceTable), $epicenterShiftOfGreatMassacreWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForEpicenterShift($modifiers, true /* shifted */, 789),
            $this->createSpellTraitsTableShell()
        );
        $epicenterShiftOfModifiedGreatMassacre = $formulasTable->getEpicenterShiftOfModified(
            FormulaCode::getIt(FormulaCode::GREAT_MASSACRE), // +20
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertEquals(new DistanceBonus(809, $distanceTable), $epicenterShiftOfModifiedGreatMassacre);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfShifts
     * @param bool $epicenterShifted
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForEpicenterShift(
        array $expectedModifiers,
        bool $epicenterShifted,
        int $sumOfShifts
    )
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumEpicenterShiftChange')
            ->with($expectedModifiers)
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
        $modifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForSpellSpeed($modifiers, 132456),
            $this->createSpellTraitsTableShell()
        );
        $spellSpeedOfLock = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no spell speed (null)
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertNull($spellSpeedOfLock);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForSpellSpeed($modifiers, 0),
            $this->createSpellTraitsTableShell()
        );
        $speedTable = new SpeedTable();
        $speedTable->getIndexedValues(); // just to populate them for sake of comparison
        $spellSpeedOfTsunamiWithoutChange = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // +0
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertEquals(new SpeedBonus(0, $speedTable), $spellSpeedOfTsunamiWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForSpellSpeed($modifiers, 789),
            $this->createSpellTraitsTableShell()
        );
        $spellSpeedOfModifiedTsunami = $formulasTable->getSpellSpeedOfModified(
            FormulaCode::getIt(FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES), // +0
            $modifiers,
            ['foo bar', 'bar BAZ']
        );
        self::assertEquals(new SpeedBonus(789, $speedTable), $spellSpeedOfModifiedTsunami);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfSpeedChange
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForSpellSpeed(array $expectedModifiers, int $sumOfSpeedChange)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumSpellSpeedChange')
            ->with($expectedModifiers)
            ->andReturn(new IntegerObject($sumOfSpeedChange));

        return $modifiersTable;
    }

    /**
     * @test
     */
    public function I_can_get_attack_of_modified_formula()
    {
        $modifiers = ['foo', 'bar'];
        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAttack($modifiers, 132456),
            $this->createSpellTraitsTableShell()
        );
        $attackOfLock = $formulasTable->getAttackOfModified(
            FormulaCode::getIt(FormulaCode::LOCK), // no spell speed (null)
            $modifiers,
            ['foo FOO']
        );
        self::assertNull($attackOfLock);

        $dischargeCode = FormulaCode::getIt(FormulaCode::DISCHARGE);
        $dischargeAttack = $formulasTable->getAttack($dischargeCode);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAttack($modifiers, 0),
            $this->createSpellTraitsTableShell()
        );
        self::assertSame(4, $dischargeAttack->getValue());
        $attackOfDischargeWithoutChange = $formulasTable->getAttackOfModified($dischargeCode, $modifiers, ['foo FOO']);
        self::assertEquals($dischargeAttack, $attackOfDischargeWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForAttack($modifiers, 789),
            $this->createSpellTraitsTableShell()
        );
        $attackOfModifiedDischarge = $formulasTable->getAttackOfModified($dischargeCode, $modifiers, ['foo FOO']);
        self::assertEquals($dischargeAttack->add(789), $attackOfModifiedDischarge);
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

    /**
     * @test
     */
    public function I_can_get_casting_of_modified_formula()
    {
        $modifiers = ['foo', 'bar'];
        $portal = FormulaCode::getIt(FormulaCode::PORTAL);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForCasting($modifiers, 0),
            $this->createSpellTraitsTableShell()
        );
        $castingOfPortalWithoutChange = $formulasTable->getCastingOfModified($portal /* 0 */, $modifiers, ['foo bar', 'bar BAZ']);
        self::assertEquals($formulasTable->getCasting($portal), $castingOfPortalWithoutChange);

        $formulasTable = new FormulasTable(
            Tables::getIt(),
            $this->createModifiersTableForCasting($modifiers, 18 /* rounds */),
            $this->createSpellTraitsTableShell()
        );
        $castingOfPortalWithChange = $formulasTable->getCastingOfModified($portal, $modifiers, ['foo bar', 'bar BAZ']);
        self::assertEquals(new Time(19, TimeUnitCode::ROUND, Tables::getIt()->getTimeTable()), $castingOfPortalWithChange);
    }

    /**
     * @param array $expectedModifiers
     * @param int $sumOfCasting
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTableForCasting(array $expectedModifiers, int $sumOfCasting)
    {
        $modifiersTable = $this->mockery(ModifiersTable::class);
        $modifiersTable->shouldReceive('sumCastingRoundsChange')
            ->with($expectedModifiers)
            ->andReturn(new CastingRounds($sumOfCasting, Tables::getIt()->getTimeTable()));

        return $modifiersTable;
    }
}