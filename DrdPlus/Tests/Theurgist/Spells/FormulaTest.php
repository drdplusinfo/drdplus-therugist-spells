<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\FormulaMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\CastingParameters\AdditionByDifficulty;
use DrdPlus\Theurgist\Spells\CastingParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\CastingParameters\FormulaDifficultyAddition;
use DrdPlus\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\CastingParameters\Realm;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\Formula;
use DrdPlus\Theurgist\Spells\FormulasTable;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;
use Mockery\Exception\NoMatchingExpectationException;
use Mockery\MockInterface;

class FormulaTest extends TestWithMockery
{
    private $parameterNamespace;

    protected function setUp()
    {
        $this->parameterNamespace = (new \ReflectionClass(SpellSpeed::class))->getNamespaceName();
    }

    /**
     * @test
     */
    public function I_can_create_it_without_any_change_for_every_formula()
    {
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            $formula = new Formula($formulaCode, $formulasTable, []);
            self::assertSame($formulaCode, $formula->getFormulaCode());
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $baseParameter);
                /** like @see Formula::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $formula->$getBaseParameter());

                /** like @see Formula::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
                $this->addExpectedAdditionSetter(0, $baseParameter, $baseParameter);
                self::assertSame($baseParameter, $formula->$getCurrentParameter());

                /** like @see Formula::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertSame(0, $formula->$getParameterAddition());
            }
        }
    }

    /**
     * @return \Mockery\MockInterface|FormulasTable
     */
    private function createFormulasTable()
    {
        return $this->mockery(FormulasTable::class);
    }

    /**
     * @param string $parameterName
     * @return IntegerCastingParameter|\Mockery\MockInterface
     */
    private function createExpectedParameter(string $parameterName): IntegerCastingParameter
    {
        $parameterClass = $this->getParameterClass($parameterName);

        return $this->mockery($parameterClass);
    }

    private function getParameterClass(string $parameterName): string
    {
        $parameterClassBasename = ucfirst(StringTools::assembleMethodName($parameterName));

        $baseParameterClass = $this->parameterNamespace . '\\' . $parameterClassBasename;
        self::assertTrue(class_exists($baseParameterClass));

        return $baseParameterClass;
    }

    private function addBaseParameterGetter(
        string $parameterName,
        FormulaCode $formulaCode,
        MockInterface $formulasTable,
        IntegerCastingParameter $value = null
    )
    {
        $getProperty = StringTools::assembleGetterForName($parameterName);
        $formulasTable->shouldReceive($getProperty)
            ->with($formulaCode)
            ->andReturn($value);
    }

    private function addExpectedAdditionSetter(
        int $addition,
        \Mockery\MockInterface $parameter,
        IntegerCastingParameter $modifiedParameter
    )
    {
        $parameter->shouldReceive('getWithAddition')
            ->with($addition)
            ->andReturn($modifiedParameter);
    }

    /**
     * @test
     */
    public function I_get_null_for_unused_modifiers_for_every_formula()
    {
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            $formula = new Formula($formulaCode, $formulasTable, []);
            self::assertSame($formulaCode, $formula->getFormulaCode());
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                if ($mutableParameterName === FormulaMutableCastingParameterCode::DURATION) {
                    continue; // can not be null, skipping
                }
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, null);
                /** like @see Formula::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertNull($formula->$getBaseParameter());

                /** like @see Formula::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
                self::assertNull($formula->$getCurrentParameter());

                /** like @see Formula::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertSame(0, $formula->$getParameterAddition());
            }
        }
    }

    /**
     * @test
     */
    public function I_can_create_it_with_change_for_every_formula()
    {
        $additions = [
            FormulaMutableCastingParameterCode::RADIUS => 1,
            FormulaMutableCastingParameterCode::DURATION => 2,
            FormulaMutableCastingParameterCode::POWER => 3,
            FormulaMutableCastingParameterCode::ATTACK => 4,
            FormulaMutableCastingParameterCode::SIZE_CHANGE => 5,
            FormulaMutableCastingParameterCode::DETAIL_LEVEL => 6,
            FormulaMutableCastingParameterCode::BRIGHTNESS => 7,
            FormulaMutableCastingParameterCode::SPELL_SPEED => 8,
            FormulaMutableCastingParameterCode::EPICENTER_SHIFT => 9,
        ];
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            $baseParameters = [];
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $baseParameter);
                $baseParameters[$mutableParameterName] = $baseParameter;
            }
            $formula = new Formula($formulaCode, $formulasTable, $additions);
            self::assertSame($formulaCode, $formula->getFormulaCode());
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = $baseParameters[$mutableParameterName];
                $addition = $additions[$mutableParameterName];
                /** like @see Formula::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $formula->$getBaseParameter());
                /** like @see Formula::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
                $this->addExpectedAdditionSetter(
                    $addition,
                    $baseParameter,
                    $changedParameter = $this->createExpectedParameter($mutableParameterName)
                );
                self::assertSame($baseParameter, $formula->$getBaseParameter());
                try {
                    self::assertSame($changedParameter, $formula->$getCurrentParameter());
                } catch (NoMatchingExpectationException $expectationException) {
                    self::fail("Parameter {$mutableParameterName} uses wrong addition: " . $expectationException->getMessage());
                }

                /** like @see Formula::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertNotSame(0, $addition);
                self::assertSame($addition, $formula->$getParameterAddition());
            }
        }
    }

    /**
     * @test
     */
    public function I_get_basic_difficulty_change_without_any_parameter()
    {
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = null;
                if ($mutableParameterName === FormulaMutableCastingParameterCode::DURATION) {
                    // duration can not be null
                    $baseParameter = $this->createExpectedParameter(FormulaMutableCastingParameterCode::DURATION);
                    $this->addExpectedAdditionSetter(0, $baseParameter, $baseParameter);
                    $this->addAdditionByDifficultyGetter(0, $baseParameter);
                }
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $baseParameter);
            }
            $this->addFormulaDifficultyGetter($formulasTable, $formulaCode, 0);
            $formula = new Formula($formulaCode, $formulasTable, []);
            self::assertSame(
                $formulasTable->getFormulaDifficulty($formulaCode)->createWithChange(0),
                $formula->getDifficultyOfChanged()
            );
        }
    }

    private function addFormulaDifficultyGetter(
        MockInterface $formulaTable,
        FormulaCode $expectedFormulaCode,
        int $expectedDifficultyChange,
        $formulaChangedDifficulty = null
    )
    {
        $formulaTable->shouldReceive('getFormulaDifficulty')
            ->with($expectedFormulaCode)
            ->andReturn($formulaDifficulty = $this->mockery(FormulaDifficulty::class));
        $formulaDifficulty->shouldReceive('createWithChange')
            ->with($expectedDifficultyChange)
            ->andReturn($formulaChangedDifficulty ?? $this->mockery(FormulaDifficulty::class));
    }

    /**
     * @test
     */
    public function I_get_basic_difficulty_change_with_every_parameter()
    {
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            $parameterDifficulties = [];
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $parameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $parameter);
                $changedParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addExpectedAdditionSetter(0, $parameter, $changedParameter);
                $parameterDifficulties[] = $difficultyChange = random_int(-10, 10);
                $this->addAdditionByDifficultyGetter($difficultyChange, $changedParameter);
            }
            $this->addFormulaDifficultyGetter($formulasTable, $formulaCode, array_sum($parameterDifficulties));
            $formula = new Formula($formulaCode, $formulasTable, []);
            try {
                self::assertNotEquals($formulasTable->getFormulaDifficulty($formulaCode), $formula->getDifficultyOfChanged());
                self::assertEquals(
                    $formulasTable->getFormulaDifficulty($formulaCode)->createWithChange(array_sum($parameterDifficulties)),
                    $formula->getDifficultyOfChanged()
                );
            } catch (NoMatchingExpectationException $expectationException) {
                self::fail(
                    'Expected difficulty sum ' . array_sum($parameterDifficulties)
                    . ' as sum of ' . implode(',', $parameterDifficulties) . ': ' . $expectationException->getMessage()
                );
            }
        }
    }

    private function addAdditionByDifficultyGetter(int $difficultyChange, MockInterface $parameter)
    {
        $parameter->shouldReceive('getAdditionByDifficulty')
            ->andReturn($additionByDifficulty = $this->mockery(AdditionByDifficulty::class));
        $additionByDifficulty->shouldReceive('getValue')
            ->andReturn($difficultyChange);
    }

    /**
     * @test
     */
    public function I_get_basic_realm_when_no_change()
    {
        foreach (FormulaCode::getPossibleValues() as $formulaValue) {
            $formulaCode = FormulaCode::getIt($formulaValue);
            $formulasTable = $this->createFormulasTable();
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = null;
                if ($mutableParameterName === FormulaMutableCastingParameterCode::DURATION) {
                    // duration can not be null
                    $baseParameter = $this->createExpectedParameter(FormulaMutableCastingParameterCode::DURATION);
                    $this->addExpectedAdditionSetter(0, $baseParameter, $baseParameter);
                    $this->addAdditionByDifficultyGetter(0, $baseParameter);
                }
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $baseParameter);
            }
            $this->addFormulaDifficultyGetter(
                $formulasTable,
                $formulaCode,
                0,
                $changedDifficulty = $this->mockery(FormulaDifficulty::class)
            );
            $this->addCurrentRealmsIncrementGetter($changedDifficulty, 123);
            $this->addRealmGetter($formulasTable, $formulaCode, 123, $finalRealm = $this->mockery(Realm::class));
            $formula = new Formula($formulaCode, $formulasTable, []);
            self::assertSame($finalRealm, $formula->getRequiredRealm());
        }
    }

    private function addCurrentRealmsIncrementGetter(MockInterface $formulaDifficulty, int $currentRealmsIncrement)
    {
        $formulaDifficulty->shouldReceive('getFormulaDifficultyAddition')
            ->andReturn($formulaDifficultyAddition = $this->mockery(FormulaDifficultyAddition::class));
        $formulaDifficultyAddition->shouldReceive('getCurrentRealmsIncrement')
            ->andReturn($currentRealmsIncrement);
    }

    private function addRealmGetter(
        MockInterface $formulasTable,
        FormulaCode $formulaCode,
        int $expectedRealmsIncrement,
        $finalRealm
    )
    {
        $formulasTable->shouldReceive('getRealm')
            ->with($formulaCode)
            ->andReturn($realm = $this->mockery(Realm::class));
        $realm->shouldReceive('add')
            ->with($expectedRealmsIncrement)
            ->andReturn($finalRealm);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameterAddition
     * @expectedExceptionMessageRegExp ~0\.1~
     */
    public function I_can_not_create_it_with_non_integer_addition()
    {
        try {
            new Formula(
                FormulaCode::getIt(FormulaCode::PORTAL),
                $this->createFormulasTable(),
                [FormulaMutableCastingParameterCode::DURATION => 0.0]
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        try {
            $formulasTable = $this->createFormulasTable();
            $this->addBaseParameterGetter(
                FormulaMutableCastingParameterCode::DURATION,
                FormulaCode::getIt(FormulaCode::PORTAL),
                $formulasTable,
                $this->createExpectedParameter(FormulaMutableCastingParameterCode::DURATION)
            );
            new Formula(
                FormulaCode::getIt(FormulaCode::PORTAL),
                $formulasTable,
                [FormulaMutableCastingParameterCode::DURATION => '5.000']
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        new Formula(
            FormulaCode::getIt(FormulaCode::PORTAL),
            $this->createFormulasTable(),
            [FormulaMutableCastingParameterCode::DURATION => 0.1]
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\UselessAdditionForUnusedCastingParameter
     * @expectedExceptionMessageRegExp ~4~
     */
    public function I_can_not_add_non_zero_addition_to_unused_parameter()
    {
        try {
            $formulasTable = $this->createFormulasTable();
            $this->addBaseParameterGetter(
                FormulaMutableCastingParameterCode::BRIGHTNESS,
                FormulaCode::getIt(FormulaCode::LIGHT),
                $formulasTable,
                $this->createExpectedParameter(FormulaMutableCastingParameterCode::BRIGHTNESS)
            );
            new Formula(
                FormulaCode::getIt(FormulaCode::LIGHT),
                $formulasTable,
                [FormulaMutableCastingParameterCode::BRIGHTNESS => 4]
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        $formulasTable = $this->createFormulasTable();
        $this->addBaseParameterGetter(
            FormulaMutableCastingParameterCode::BRIGHTNESS,
            FormulaCode::getIt(FormulaCode::LIGHT),
            $formulasTable,
            null // unused
        );
        new Formula(
            FormulaCode::getIt(FormulaCode::LIGHT),
            $formulasTable,
            [FormulaMutableCastingParameterCode::BRIGHTNESS => 4]
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     * @expectedExceptionMessageRegExp ~divine~
     */
    public function I_can_not_create_it_with_addition_of_unknown_parameter()
    {
        new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $this->createFormulasTable(), ['divine' => 0]);
    }

}