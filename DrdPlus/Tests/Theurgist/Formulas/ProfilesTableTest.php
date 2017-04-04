<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ProfileCode;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use DrdPlus\Theurgist\Formulas\ProfilesTable;

class ProfilesTableTest extends AbstractTheurgistTableTest
{
    /**
     * @var FormulasTable
     */
    private $formulasTable;

    protected function setUp()
    {
        $this->formulasTable = new FormulasTable();
    }

    /**
     * @test
     */
    public function I_can_get_formulas_for_profile()
    {
        $profilesTable = new ProfilesTable();
        foreach (ProfileCode::getPossibleValues() as $profileValue) {
            $formulaCodes = $profilesTable->getFormulasForProfile(ProfileCode::getIt($profileValue));
            self::assertTrue(is_array($formulaCodes));
            if ($profileValue === 'look_mars' || strpos($profileValue, 'venus')) {
                self::assertCount(0, $formulaCodes);
            } else {
                self::assertNotEmpty($formulaCodes, 'Expected some formulas for profile ' . $profileValue);
            }
            $formulaValues = [];
            foreach ($formulaCodes as $formulaCode) {
                self::assertInstanceOf(FormulaCode::class, $formulaCode);
                $formulaValues[] = $formulaCode->getValue();
            }
            sort($formulaValues);
            $expectedFormulas = $this->getExpectedFormulas($profileValue);
            sort($expectedFormulas);
            self::assertEquals(
                $expectedFormulas,
                $formulaValues,
                'Expected different formulas for profile ' . $profileValue
                . (count($missing = array_diff($expectedFormulas, $formulaValues)) > 0
                    ? ', missing: ' . implode(', ', $missing)
                    : ''
                )
                . (count($redundant = array_diff($formulaValues, $expectedFormulas)) > 0
                    ? ', not expecting: ' . implode(', ', $redundant)
                    : ''
                )
            );
        }
    }

    /**
     * @param string $profileValue
     * @return array|string[]
     */
    private function getExpectedFormulas(string $profileValue): array
    {
        $oppositeProfile = $this->reverseProfileGender($profileValue);
        $possibleFormulas = [];
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $profileCodes = $this->formulasTable->getProfilesForFormula(FormulaCode::getIt($formulaValue));
            foreach ($profileCodes as $profileCode) {
                if ($profileCode->getValue() === $oppositeProfile) {
                    $possibleFormulas[] = $formulaValue;
                    break;
                }
            }
        }

        return $possibleFormulas;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownProfileToGetFormulasFor
     * @expectedExceptionMessageRegExp ~Sexy texy~
     */
    public function I_can_not_get_formulas_to_unknown_profile()
    {
        (new ProfilesTable())->getFormulasForProfile($this->createProfileCode('Sexy texy'));
    }

    /**
     * @param string $value
     * @return \Mockery\MockInterface|ProfileCode
     */
    private function createProfileCode(string $value)
    {
        $profileCode = $this->mockery(ProfileCode::class);
        $profileCode->shouldReceive('getValue')
            ->andReturn($value);
        $profileCode->shouldReceive('__toString')
            ->andReturn($value);

        return $profileCode;
    }

    /**
     * @test
     */
    public function I_can_get_modifiers_to_profiles()
    {
        $profilesTable = new ProfilesTable();
        foreach (ProfileCode::getPossibleValues() as $profileValue) {
            $modifierCodes = $profilesTable->getModifiersForProfile(ProfileCode::getIt($profileValue));
            self::assertTrue(is_array($modifierCodes));
            $modifierValues = [];
            foreach ($modifierCodes as $modifierCode) {
                self::assertInstanceOf(ModifierCode::class, $modifierCode);
                $modifierValues[] = $modifierCode->getValue();
            }
            sort($modifierValues);
            $expectedModifiers = $this->getExpectedModifiersFor($profileValue);
            sort($expectedModifiers);
            self::assertEquals(
                $expectedModifiers,
                $modifierValues,
                "Expected different modifiers for profile '{$profileValue}'"
                . (count($redundant = array_diff($modifierValues, $expectedModifiers)) > 0
                    ? ', not expecting: ' . implode(', ', $redundant)
                    : ''
                )
                . (count($missing = array_diff($expectedModifiers, $modifierValues)) > 0
                    ? ', missing: ' . implode(', ', $missing)
                    : ''
                )
            );
        }
    }

    private static $impossibleModifiers = [
        'barrier_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'barrier_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'spark_venus' => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'spark_mars' => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'release_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::FRAGRANCE],
        'release_mars' => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'scent_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE],
        'scent_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'illusion_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'illusion_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'receptor_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'receptor_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE],
        'breach_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'breach_mars' => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'fire_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'fire_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'gate_venus' => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'gate_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'movement_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'movement_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'transposition_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'transposition_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'discharge_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'discharge_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'watcher_venus' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'watcher_mars' => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'look_venus' => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
        'look_mars' => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::FRAGRANCE],
    ];

    /**
     * @param string $profileValue
     * @return array|string[]
     */
    private function getExpectedModifiersFor(string $profileValue): array
    {
        return array_diff(ModifierCode::getPossibleValues(), self::$impossibleModifiers[$profileValue]);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\Exceptions\UnknownProfileToGetModifiersFor
     * @expectedExceptionMessageRegExp ~Lazy lizard~
     */
    public function I_can_not_get_modifiers_to_unknown_profile()
    {
        (new ProfilesTable())->getModifiersForProfile($this->createProfileCode('Lazy lizard'));
    }

}