<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\AffectionPeriodCode;
use DrdPlus\Theurgist\Codes\FormulaCode;
use DrdPlus\Theurgist\Codes\FormulaMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\AdditionByDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\SpellParameters\Evocation;
use DrdPlus\Theurgist\Spells\SpellParameters\FormulaDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\RealmsAffection;
use DrdPlus\Theurgist\Spells\SpellParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\Formula;
use DrdPlus\Theurgist\Spells\FormulasTable;
use DrdPlus\Theurgist\Spells\Modifier;
use DrdPlus\Theurgist\Spells\SpellTrait;
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
            $formula = new Formula($formulaCode, $formulasTable, [], [], []);
            self::assertSame($formulaCode, $formula->getFormulaCode());
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $formulaCode, $formulasTable, $baseParameter);
                /** like @see Formula::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $formula->$getBaseParameter());

                /** like @see Formula::getCurrentRadius() */
                $getParameterWithAddition = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                $this->addExpectedAdditionSetter(0, $baseParameter, $baseParameter);
                self::assertSame($baseParameter, $formula->$getParameterWithAddition());

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
            $formula = new Formula($formulaCode, $formulasTable, [], [], []);
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
                $getParameterWithAddition = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                self::assertNull($formula->$getParameterWithAddition());

                /** like @see Formula::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertSame(0, $formula->$getParameterAddition());
            }
        }
    }

    /**
     * @test
     */
    public function I_can_create_it_with_addition_for_every_formula()
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
            $formula = new Formula($formulaCode, $formulasTable, $additions, [], []);
            self::assertSame($formulaCode, $formula->getFormulaCode());
            foreach (FormulaMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = $baseParameters[$mutableParameterName];
                $addition = $additions[$mutableParameterName];
                /** like @see Formula::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $formula->$getBaseParameter());
                /** like @see Formula::getCurrentRadius() */
                $getParameterWithAddition = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                $this->addExpectedAdditionSetter(
                    $addition,
                    $baseParameter,
                    $changedParameter = $this->createExpectedParameter($mutableParameterName)
                );
                self::assertSame($baseParameter, $formula->$getBaseParameter());
                try {
                    self::assertSame($changedParameter, $formula->$getParameterWithAddition());
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
    public function I_can_get_base_difficulty()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $formulasTable, [], [], []);
        $formulasTable
            ->shouldReceive('getFormulaDifficulty')
            ->andReturn($formulaDifficulty = $this->mockery(FormulaDifficulty::class));
        self::assertSame($formulaDifficulty, $formula->getBaseDifficulty());
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
            $formula = new Formula($formulaCode, $formulasTable, [], [], []);
            self::assertSame(
                $formulasTable->getFormulaDifficulty($formulaCode)->createWithChange(0),
                $formula->getCurrentDifficulty()
            );
        }
    }

    private function addFormulaDifficultyGetter(
        MockInterface $formulaTable,
        FormulaCode $expectedFormulaCode,
        int $expectedDifficultyChange,
        FormulaDifficulty $formulaChangedDifficulty = null
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
    public function I_get_difficulty_change_with_every_parameter()
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
            $this->addFormulaDifficultyGetter($formulasTable, $formulaCode, 123 + 456 + 789 + 789 + 159 + array_sum($parameterDifficulties));
            $formula = new Formula(
                $formulaCode,
                $formulasTable,
                [],
                [$this->createModifierWithDifficulty(123), [$this->createModifierWithDifficulty(456)]],
                [$this->getSpellTrait(789), [$this->getSpellTrait(789), [$this->getSpellTrait(159)]]]
            );
            try {
                self::assertNotEquals($formulasTable->getFormulaDifficulty($formulaCode), $formula->getCurrentDifficulty());
                self::assertEquals(
                    $formulasTable->getFormulaDifficulty($formulaCode)->createWithChange(
                        123 + 456 + 789 + 789 + 159 + array_sum($parameterDifficulties)
                    ),
                    $formula->getCurrentDifficulty()
                );
            } catch (NoMatchingExpectationException $expectationException) {
                self::fail(
                    'Expected difficulty ' . (123 + 456 + 789 + 789 + 159 + array_sum($parameterDifficulties))
                    . ': ' . $expectationException->getMessage()
                );
            }
        }
    }

    /**
     * @param int $difficultyChangeValue
     * @return MockInterface|Modifier
     */
    private function createModifierWithDifficulty(int $difficultyChangeValue)
    {
        $modifier = $this->mockery(Modifier::class);
        $modifier->shouldReceive('getDifficultyChange')
            ->andReturn($difficultyChange = $this->mockery(DifficultyChange::class));
        $difficultyChange->shouldReceive('getValue')
            ->andReturn($difficultyChangeValue);

        return $modifier;
    }

    /**
     * @param int $difficultyChangeValue
     * @return MockInterface|SpellTrait
     */
    private function getSpellTrait(int $difficultyChangeValue)
    {
        $spellTrait = $this->mockery(SpellTrait::class);
        $spellTrait->shouldReceive('getDifficultyChange')
            ->andReturn($difficultyChange = $this->mockery(DifficultyChange::class));
        $difficultyChange->shouldReceive('getValue')
            ->andReturn($difficultyChangeValue);

        return $spellTrait;
    }

    private function addAdditionByDifficultyGetter(int $difficultyChange, MockInterface $parameter)
    {
        $parameter->shouldReceive('getAdditionByDifficulty')
            ->andReturn($additionByDifficulty = $this->mockery(AdditionByDifficulty::class));
        $additionByDifficulty->shouldReceive('getCurrentDifficultyIncrement')
            ->andReturn($difficultyChange);
    }

    /**
     * @test
     */
    public function I_can_get_base_casting_rounds()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $formulasTable, [], [], []);
        $formulasTable->shouldReceive('getCastingRounds')
            ->andReturn($castingRounds = $this->mockery(CastingRounds::class));
        self::assertSame($castingRounds, $formula->getBaseCastingRounds());
    }

    /**
     * @test
     */
    public function I_can_get_final_casting_rounds_affected_by_modifiers()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula(
            FormulaCode::getIt(FormulaCode::PORTAL),
            $formulasTable,
            [],
            [$this->createModifier(1), [$this->createModifier(2), [$this->createModifier(3), $this->createModifier(4)]]],
            []
        );
        $formulasTable->shouldReceive('getCastingRounds')
            ->andReturn($this->createCastingRounds(123));
        $finalCastingRounds = $formula->getCurrentCastingRounds();
        self::assertInstanceOf(CastingRounds::class, $finalCastingRounds);
        self::assertSame(123 + 1 + 2 + 3 + 4, $finalCastingRounds->getValue());
    }

    /**
     * @param int $castingRoundsValue
     * @return MockInterface|Modifier
     */
    private function createModifier(int $castingRoundsValue)
    {
        $modifier = $this->mockery(Modifier::class);
        $modifier->shouldReceive('getCastingRounds')
            ->andReturn($this->createCastingRounds($castingRoundsValue));

        return $modifier;
    }

    /**
     * @param int $value
     * @return MockInterface|CastingRounds
     */
    private function createCastingRounds(int $value)
    {
        $castingRounds = $this->mockery(CastingRounds::class);
        $castingRounds->shouldReceive('getValue')
            ->andReturn($value);

        return $castingRounds;
    }

    /**
     * @test
     */
    public function I_can_get_evocation()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula($formulaCode = FormulaCode::getIt(FormulaCode::DISCHARGE), $formulasTable, [], [], []);
        $formulasTable->shouldReceive('getEvocation')
            ->with($formulaCode)
            ->andReturn($evocation = $this->mockery(Evocation::class));
        self::assertSame($evocation, $formula->getCurrentEvocation());
    }

    /**
     * @test
     */
    public function I_can_get_base_realms_affection()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula($formulaCode = FormulaCode::getIt(FormulaCode::ILLUSION), $formulasTable, [], [], []);
        $formulasTable->shouldReceive('getRealmsAffection')
            ->with($formulaCode)
            ->andReturn($realmsAffection = $this->createRealmsAffection(AffectionPeriodCode::LIFE, -123));
        self::assertSame($realmsAffection, $formula->getBaseRealmsAffection());
        self::assertEquals(
            [AffectionPeriodCode::LIFE => new RealmsAffection([-123, AffectionPeriodCode::LIFE])],
            $formula->getCurrentRealmsAffections()
        );
    }

    /**
     * @param string $periodName
     * @param int $formulaAffectionValue
     * @return MockInterface|RealmsAffection
     */
    private function createRealmsAffection(string $periodName, int $formulaAffectionValue)
    {
        $realmsAffection = $this->mockery(RealmsAffection::class);
        $realmsAffection->shouldReceive('getAffectionPeriod')
            ->andReturn($affectionPeriod = $this->mockery(AffectionPeriodCode::class));
        $affectionPeriod->shouldReceive('getValue')
            ->andReturn($periodName);
        $realmsAffection->shouldReceive('getValue')
            ->andReturn($formulaAffectionValue);

        return $realmsAffection;
    }

    /**
     * @test
     */
    public function I_can_get_current_realms_affection()
    {
        $formulasTable = $this->createFormulasTable();
        $formula = new Formula(
            $formulaCode = FormulaCode::getIt(FormulaCode::ILLUSION),
            $formulasTable,
            [],
            [$this->createModifierWithRealmsAffection(-5, AffectionPeriodCode::DAILY),
                [
                    $this->createModifierWithRealmsAffection(-2, AffectionPeriodCode::DAILY),
                    $this->createModifierWithRealmsAffection(-8, AffectionPeriodCode::MONTHLY),
                    $this->createModifierWithoutRealmsAffection(),
                    $this->createModifierWithRealmsAffection(-1, AffectionPeriodCode::YEARLY),
                ],
            ],
            []
        );
        $formulasTable->shouldReceive('getRealmsAffection')
            ->with($formulaCode)
            ->andReturn($this->createRealmsAffection(AffectionPeriodCode::YEARLY, -11)); // base realm affection
        $expected = [
            AffectionPeriodCode::DAILY => new RealmsAffection([-7, AffectionPeriodCode::DAILY]),
            AffectionPeriodCode::MONTHLY => new RealmsAffection([-8, AffectionPeriodCode::MONTHLY]),
            AffectionPeriodCode::YEARLY => new RealmsAffection([-12, AffectionPeriodCode::YEARLY]),
        ];
        ksort($expected);
        $current = $formula->getCurrentRealmsAffections();
        ksort($current);
        self::assertEquals($expected, $current);
    }

    /**
     * @param int $realmsAffectionValue
     * @param string $affectionPeriodValue
     * @return MockInterface|Modifier
     */
    private function createModifierWithRealmsAffection(int $realmsAffectionValue, string $affectionPeriodValue)
    {
        $modifier = $this->mockery(Modifier::class);
        $modifier->shouldReceive('getRealmsAffection')
            ->andReturn($realmsAffection = $this->mockery(RealmsAffection::class));
        $realmsAffection->shouldReceive('getAffectionPeriod')
            ->andReturn($affectionPeriod = $this->mockery(AffectionPeriodCode::class));
        $affectionPeriod->shouldReceive('getValue')
            ->andReturn($affectionPeriodValue);
        $realmsAffection->shouldReceive('getValue')
            ->andReturn($realmsAffectionValue);

        return $modifier;
    }

    /**
     * @return MockInterface|Modifier
     */
    private function createModifierWithoutRealmsAffection()
    {
        $modifier = $this->mockery(Modifier::class);
        $modifier->shouldReceive('getRealmsAffection')
            ->andReturn(null);

        return $modifier;
    }

    /**
     * @test
     */
    public function I_get_final_realm()
    {
        $formulaCode = FormulaCode::getIt(FormulaCode::PORTAL);
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
        $this->addRealmGetter($formulasTable, $formulaCode, 123, $formulaRealm = $this->mockery(Realm::class));
        $formulaWithoutModifiers = new Formula($formulaCode, $formulasTable, [], [], []);
        self::assertSame($formulaRealm, $formulaWithoutModifiers->getRequiredRealm());

        $lowModifiers = [$this->createModifierWithRequiredRealm(0), $this->createModifierWithRequiredRealm(122)];
        $formulaWithLowModifiers = new Formula($formulaCode, $formulasTable, [], $lowModifiers, []);
        $formulaRealm->shouldReceive('getValue')
            ->andReturn(123);
        self::assertSame($formulaRealm, $formulaWithLowModifiers->getRequiredRealm());

        $highModifiers = [
            [$this->createModifierWithRequiredRealm(123)],
            $this->createModifierWithRequiredRealm(124, $highestRealm = $this->mockery(Realm::class)),
        ];
        $formulaWithHighModifiers = new Formula($formulaCode, $formulasTable, [], $highModifiers, []);
        /**
         * @var Realm $formulaRealm
         * @var Realm $highestRealm
         */
        self::assertGreaterThan($formulaRealm->getValue(), $highestRealm->getValue());
        self::assertEquals($highestRealm, $formulaWithHighModifiers->getRequiredRealm());
    }

    private function addCurrentRealmsIncrementGetter(MockInterface $formulaDifficulty, int $currentRealmsIncrement)
    {
        $formulaDifficulty->shouldReceive('getCurrentRealmsIncrement')
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
     * @param int $value
     * @pram MockInterface|null $realm
     * @return MockInterface|Modifier
     */
    private function createModifierWithRequiredRealm(int $value, MockInterface $realm = null)
    {
        $modifier = $this->mockery(Modifier::class);
        $modifier->shouldReceive('getRequiredRealm')
            ->andReturn($realm ?? $realm = $this->mockery(Realm::class));
        $realm->shouldReceive('getValue')
            ->andReturn($value);
        $modifier->shouldReceive('getDifficultyChange')
            ->andReturn($difficultyChange = $this->mockery(DifficultyChange::class));
        $difficultyChange->shouldReceive('getValue')
            ->andReturn(0);

        return $modifier;
    }

    public function I_can_get_current_radius()
    {
        // TODO
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForFormulaParameter
     * @expectedExceptionMessageRegExp ~0\.1~
     */
    public function I_can_not_create_it_with_non_integer_addition()
    {
        try {
            new Formula(
                FormulaCode::getIt(FormulaCode::PORTAL),
                $this->createFormulasTable(),
                [FormulaMutableCastingParameterCode::DURATION => 0.0],
                [],
                []
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
                [FormulaMutableCastingParameterCode::DURATION => '5.000'],
                [],
                []
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        new Formula(
            FormulaCode::getIt(FormulaCode::PORTAL),
            $this->createFormulasTable(),
            [FormulaMutableCastingParameterCode::DURATION => 0.1],
            [],
            []
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
                [FormulaMutableCastingParameterCode::BRIGHTNESS => 4],
                [],
                []
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
            [FormulaMutableCastingParameterCode::BRIGHTNESS => 4],
            [],
            []
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\UnknownFormulaParameter
     * @expectedExceptionMessageRegExp ~divine~
     */
    public function I_can_not_create_it_with_addition_of_unknown_addition()
    {
        new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $this->createFormulasTable(), ['divine' => 0], [], []);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidModifier
     * @expectedExceptionMessageRegExp ~DateTime~
     */
    public function I_can_not_create_it_with_invalid_modifier()
    {
        new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $this->createFormulasTable(), [], [new \DateTime()], []);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     * @expectedExceptionMessageRegExp ~stdClass~
     */
    public function I_can_not_create_it_with_invalid_spell_trait()
    {
        new Formula(FormulaCode::getIt(FormulaCode::PORTAL), $this->createFormulasTable(), [], [], [new \stdClass()]);
    }

}