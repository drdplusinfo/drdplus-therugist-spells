<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Formulas\FormulasTable;
use Granam\Tests\Tools\TestWithMockery;

class FormulasTableTest extends TestWithMockery
{
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
            $modifierValues = [];
            foreach ($modifierCodes as $modifierCode) {
                self::assertInstanceOf(ModifierCode::class, $modifierCode);
                $modifierValues[] = $modifierCode->getValue();
            }
            sort($modifierValues);
            $expectedModifiers = self::getPossibleModifiersFor($formulaValue);
            sort($expectedModifiers);
            self::assertEquals($expectedModifiers, $modifierValues);
        }
    }

    private static $impossibleModifiers = [
        FormulaCode::BARRIER => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::SMOKE => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST],
        FormulaCode::ILLUSION => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::THUNDER, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::SCENT],
        FormulaCode::METAMORPHOSIS => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::SCENT],
        FormulaCode::FIRE => [ModifierCode::GATE, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::PORTAL => [ModifierCode::COLOR, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::TRANSPOSITION, ModifierCode::RELEASE, ModifierCode::SCENT],
        FormulaCode::LIGHT => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::FLOW_OF_TIME => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::TSUNAMI_FROM_CLAY_AND_STONES => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::HIT => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::RELEASE, ModifierCode::SCENT],
        FormulaCode::GREAT_MASSACRE => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::DISCHARGE => [ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::WATCHER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::CAMOUFLAGE, ModifierCode::BREACH, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::SCENT],
        FormulaCode::LOCK => [ModifierCode::COLOR, ModifierCode::GATE, ModifierCode::EXPLOSION, ModifierCode::FILTER, ModifierCode::THUNDER, ModifierCode::INTERACTIVE_ILLUSION, ModifierCode::HAMMER, ModifierCode::CAMOUFLAGE, ModifierCode::INVISIBILITY, ModifierCode::MOVEMENT, ModifierCode::RECEPTOR, ModifierCode::STEP_TO_FUTURE_OR_PAST, ModifierCode::RELEASE, ModifierCode::SCENT],
    ];

    /**
     * @param string $formula
     * @return array|string[]
     */
    private static function getPossibleModifiersFor(string $formula): array
    {
        return array_diff(ModifierCode::getPossibleValues(), self::$impossibleModifiers[$formula]);
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
}