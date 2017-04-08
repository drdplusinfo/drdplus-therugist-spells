<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Theurgist\Codes\FormCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Codes\TraitCode;
use DrdPlus\Theurgist\Formulas\CastingParameters\Attack;
use DrdPlus\Theurgist\Formulas\CastingParameters\Conditions;
use DrdPlus\Theurgist\Formulas\CastingParameters\Grafts;
use DrdPlus\Theurgist\Formulas\CastingParameters\Invisibility;
use DrdPlus\Theurgist\Formulas\CastingParameters\Points;
use DrdPlus\Theurgist\Formulas\CastingParameters\Power;
use DrdPlus\Theurgist\Formulas\CastingParameters\Quality;
use DrdPlus\Theurgist\Formulas\CastingParameters\Resistance;
use DrdPlus\Theurgist\Formulas\CastingParameters\Situations;
use DrdPlus\Theurgist\Formulas\CastingParameters\Speed;
use DrdPlus\Theurgist\Formulas\CastingParameters\SpellTrait;
use DrdPlus\Theurgist\Formulas\CastingParameters\Threshold;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use DrdPlus\Theurgist\Formulas\ModifiersTable;
use DrdPlus\Theurgist\Formulas\ProfilesTable;

class ModifiersTableTest extends AbstractTheurgistTableTest
{

    /**
     * @var FormulasTable
     */
    private $formulasTable;
    /**
     * @var ProfilesTable
     */
    private $profilesTable;

    protected function setUp()
    {
        $this->formulasTable = new FormulasTable();
        $this->profilesTable = new ProfilesTable();
    }

    /**
     * @test
     */
    public function I_can_get_power()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $power = $modifiersTable->getPower(ModifierCode::getIt($modifierCode));
            $expectedPowerValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'power');
            $expectedPower = count($expectedPowerValue) !== 0
                ? new Power($expectedPowerValue)
                : null;
            self::assertEquals($expectedPower, $power);
        }
    }

    /**
     * @test
     */
    public function I_can_get_attack()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $attack = $modifiersTable->getAttack(ModifierCode::getIt($modifierCode));
            $expectedAttackValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'attack');
            $expectedAttack = count($expectedAttackValue) !== 0
                ? new Attack($expectedAttackValue)
                : null;
            self::assertEquals($expectedAttack, $attack);
        }
    }

    /**
     * @test
     */
    public function I_can_get_grafts()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $grafts = $modifiersTable->getGrafts(ModifierCode::getIt($modifierCode));
            $expectedGraftsValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'grafts');
            $expectedGrafts = count($expectedGraftsValue) !== 0
                ? new Grafts($expectedGraftsValue)
                : null;
            self::assertEquals($expectedGrafts, $grafts);
        }
    }

    /**
     * @test
     */
    public function I_can_get_speed()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $speed = $modifiersTable->getSpeed(ModifierCode::getIt($modifierCode));
            $expectedSpeedValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'speed');
            $expectedSpeed = count($expectedSpeedValue) !== 0
                ? new Speed($expectedSpeedValue)
                : null;
            self::assertEquals($expectedSpeed, $speed);
        }
    }

    /**
     * @test
     */
    public function I_can_get_points()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $points = $modifiersTable->getPoints(ModifierCode::getIt($modifierCode));
            $expectedPointsValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'points');
            $expectedPoints = count($expectedPointsValue) !== 0
                ? new Points($expectedPointsValue)
                : null;
            self::assertEquals($expectedPoints, $points);
        }
    }

    /**
     * @test
     */
    public function I_can_get_invisibility()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $invisibility = $modifiersTable->getInvisibility(ModifierCode::getIt($modifierCode));
            $expectedInvisibilityValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'invisibility');
            $expectedInvisibility = count($expectedInvisibilityValue) !== 0
                ? new Invisibility($expectedInvisibilityValue)
                : null;
            self::assertEquals($expectedInvisibility, $invisibility);
        }
    }

    /**
     * @test
     */
    public function I_can_get_quality()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $quality = $modifiersTable->getQuality(ModifierCode::getIt($modifierCode));
            $expectedQualityValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'quality');
            $expectedQuality = count($expectedQualityValue) !== 0
                ? new Quality($expectedQualityValue)
                : null;
            self::assertEquals($expectedQuality, $quality);
        }
    }

    /**
     * @test
     */
    public function I_can_get_conditions()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $conditions = $modifiersTable->getConditions(ModifierCode::getIt($modifierCode));
            $expectedConditionsValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'conditions');
            $expectedConditions = count($expectedConditionsValue) !== 0
                ? new Conditions($expectedConditionsValue)
                : null;
            self::assertEquals($expectedConditions, $conditions);
        }
    }

    /**
     * @test
     */
    public function I_can_get_resistance()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $resistance = $modifiersTable->getResistance(ModifierCode::getIt($modifierCode));
            $expectedResistanceValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'resistance');
            $expectedResistance = count($expectedResistanceValue) !== 0
                ? new Resistance($expectedResistanceValue)
                : null;
            self::assertEquals($expectedResistance, $resistance);
        }
    }

    /**
     * @test
     */
    public function I_can_get_situations()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $situations = $modifiersTable->getSituations(ModifierCode::getIt($modifierCode));
            $expectedSituationsValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'situations');
            $expectedSituations = count($expectedSituationsValue) !== 0
                ? new Situations($expectedSituationsValue)
                : null;
            self::assertEquals($expectedSituations, $situations);
        }
    }

    /**
     * @test
     */
    public function I_can_get_threshold()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierCode) {
            $threshold = $modifiersTable->getThreshold(ModifierCode::getIt($modifierCode));
            $expectedThresholdValue = $this->getValueFromTable($modifiersTable, $modifierCode, 'threshold');
            $expectedThreshold = count($expectedThresholdValue) !== 0
                ? new Threshold($expectedThresholdValue)
                : null;
            self::assertEquals($expectedThreshold, $threshold);
        }
    }

    /**
     * @test
     */
    public function I_can_get_forms()
    {
        $modifiersTable = new ModifiersTable();
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

    private function getExpectedFormValues(string $modifierValue)
    {
        $excludedFormValues = self::$excludedFormValues[$modifierValue];

        return array_diff(FormCode::getPossibleValues(), $excludedFormValues);
    }

    /**
     * @test
     */
    public function I_can_get_spell_traits()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $spellTraits = $modifiersTable->getSpellTraits(ModifierCode::getIt($modifierValue));
            /** @var array|string[] $expectedTraitValues */
            $expectedTraitValues = $this->getValueFromTable($modifiersTable, $modifierValue, 'traits');
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
            $expectedTraitCodeValues = $this->getExpectedTraitCodeValues($modifierValue);
            sort($expectedTraitCodeValues);
            self::assertEquals($expectedTraitCodeValues, $spellTraitCodeValues, "Expected different traits for '{$modifierValue}'");
        }
    }

    private static $excludedTraitValues = [
        ModifierCode::COLOR => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::GATE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::EXPLOSION => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::FILTER => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::WATCHER => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::THUNDER => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::INTERACTIVE_ILLUSION => [TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::HAMMER => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::CAMOUFLAGE => [TraitCode::AFFECTING, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::INVISIBILITY => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::MOVEMENT => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::BREACH => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::RECEPTOR => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::STEP_TO_FUTURE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::STEP_TO_PAST => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::TRANSPOSITION => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::RELEASE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
        ModifierCode::FRAGRANCE => [TraitCode::AFFECTING, TraitCode::INVISIBLE, TraitCode::SILENT, TraitCode::ODORLESS, TraitCode::CYCLIC, TraitCode::MEMORY, TraitCode::DEFORMATION, TraitCode::UNIDIRECTIONAL, TraitCode::BIDIRECTIONAL, TraitCode::INACRID, TraitCode::EVERY_SENSE, TraitCode::SITUATIONAL, TraitCode::SHAPESHIFT, TraitCode::STATE_CHANGE, TraitCode::NATURE_CHANGE, TraitCode::NO_SMOKE, TraitCode::TRANSPARENCY, TraitCode::MULTIPLE_ENTRY, TraitCode::OMNIPRESENT, TraitCode::ACTIVE],
    ];

    private function getExpectedTraitCodeValues(string $formulaValue)
    {
        $excludedTraitValues = self::$excludedTraitValues[$formulaValue];

        return array_diff(TraitCode::getPossibleValues(), $excludedTraitValues);
    }

    /**
     * @test
     */
    public function I_can_get_formulas_for_modifier()
    {
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $formulaCodes = $modifiersTable->getFormulasForModifier(ModifierCode::getIt($modifierValue));
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
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $modifierCodes = $this->formulasTable->getModifiersForFormula(FormulaCode::getIt($formulaValue));
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
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownModifierToGetFormulasFor
     * @expectedExceptionMessageRegExp ~black and white~
     */
    public function I_can_not_get_formulas_to_unknown_modifier()
    {
        (new ModifiersTable())->getFormulasForModifier($this->createModifierCode('black and white'));
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
        $modifiersTable = new ModifiersTable();
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $profileCodes = $modifiersTable->getProfilesForModifier(ModifierCode::getIt($modifierValue));
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
        foreach (ProfileCode::getPossibleValues() as $profileValue) {
            $modifierCodes = $this->profilesTable->getModifiersForProfile(ProfileCode::getIt($profileValue));
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
        (new ModifiersTable())->getProfilesForModifier($this->createModifierCode('magnified'));
    }

}