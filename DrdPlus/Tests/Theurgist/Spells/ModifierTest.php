<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Tables\Tables;
use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableSpellParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\AdditionByDifficulty;
use DrdPlus\Theurgist\Spells\SpellParameters\CastingRounds;
use DrdPlus\Theurgist\Spells\SpellParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\SpellParameters\Realm;
use DrdPlus\Theurgist\Spells\SpellParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\Modifier;
use DrdPlus\Theurgist\Spells\ModifiersTable;
use DrdPlus\Theurgist\Spells\SpellTrait;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;
use Mockery\Exception\NoMatchingExpectationException;
use Mockery\MockInterface;

class ModifierTest extends TestWithMockery
{
    private $parameterNamespace;

    protected function setUp()
    {
        $this->parameterNamespace = (new \ReflectionClass(SpellSpeed::class))->getNamespaceName();
    }

    /**
     * @test
     */
    public function I_can_create_it_without_any_change_for_every_modifier()
    {
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            $modifier = new Modifier($modifierCode, $modifiersTable, [], []);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            self::assertSame($modifierValue, (string)$modifier);
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, $baseParameter);
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $modifier->$getBaseParameter());

                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                $this->addExpectedAdditionSetter(0, $baseParameter, $baseParameter);
                self::assertSame($baseParameter, $modifier->$getCurrentParameter());

                /** like @see Modifier::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertSame(0, $modifier->$getParameterAddition());
            }
        }
    }

    /**
     * @return \Mockery\MockInterface|ModifiersTable
     */
    private function createModifiersTable()
    {
        return $this->mockery(ModifiersTable::class);
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
        ModifierCode $modifierCode,
        MockInterface $modifiersTable,
        IntegerCastingParameter $value = null
    )
    {
        $getProperty = StringTools::assembleGetterForName($parameterName);
        $modifiersTable->shouldReceive($getProperty)
            ->with($modifierCode)
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
    public function I_get_null_for_unused_modifiers_for_every_modifier()
    {
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            $modifier = new Modifier($modifierCode, $modifiersTable, [], []);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                /*if ($mutableParameterName === ModifierMutableSpellParameterCode::) {
                    continue; // can not be null, skipping
                }*/
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, null);
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertNull($modifier->$getBaseParameter());

                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                self::assertNull($modifier->$getCurrentParameter());

                /** like @see Modifier::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertSame(0, $modifier->$getParameterAddition());
            }
        }
    }

    /**
     * @test
     */
    public function I_can_create_it_with_change_for_every_modifier()
    {
        $parameterChanges = [];
        foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $index => $parameterValue) {
            $parameterChanges[$parameterValue] = $index + 1; // 1...x
        }
        $spellTraits = [$this->createSpellTraitShell(), $this->createSpellTraitShell()];
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            $baseParameters = [];
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, $baseParameter);
                $baseParameters[$mutableParameterName] = $baseParameter;
            }
            $modifier = new Modifier($modifierCode, $modifiersTable, $parameterChanges, $spellTraits);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = $baseParameters[$mutableParameterName];
                $change = $parameterChanges[$mutableParameterName];
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $modifier->$getBaseParameter());
                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName($mutableParameterName . '_with_addition');
                $this->addExpectedAdditionSetter(
                    $change,
                    $baseParameter,
                    $changedParameter = $this->createExpectedParameter($mutableParameterName)
                );
                self::assertSame($baseParameter, $modifier->$getBaseParameter());
                try {
                    self::assertSame($changedParameter, $modifier->$getCurrentParameter());
                } catch (NoMatchingExpectationException $expectationException) {
                    self::fail("Parameter {$mutableParameterName} uses wrong addition: " . $expectationException->getMessage());
                }

                /** like @see Modifier::getRadiusAddition */
                $getParameterAddition = StringTools::assembleGetterForName($mutableParameterName) . 'Addition';
                self::assertNotSame(0, $change);
                self::assertSame($change, $modifier->$getParameterAddition());
            }
        }
    }

    /**
     * @return MockInterface|SpellTrait
     */
    private function createSpellTraitShell()
    {
        return $this->mockery(SpellTrait::class);
    }

    /**
     * @test
     */
    public function I_get_basic_difficulty_change_without_any_parameter()
    {
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, null);
            }
            $difficultyChange = $this->createDifficultyChange(0);
            $this->addDifficultyChangeGetter($difficultyChange, $modifierCode, $modifiersTable);
            $modifier = new Modifier($modifierCode, $modifiersTable, [], []);
            self::assertSame($difficultyChange, $modifier->getDifficultyChange());
        }
    }

    /**
     * @param int $expectedAdd
     * @return MockInterface|DifficultyChange
     */
    private function createDifficultyChange(int $expectedAdd)
    {
        $difficultyChange = $this->mockery(DifficultyChange::class);
        $difficultyChange->shouldReceive('add')
            ->with($expectedAdd)
            ->once()
            ->andReturn($difficultyChange);

        return $difficultyChange;
    }

    private function addDifficultyChangeGetter(
        MockInterface $difficultyChange,
        ModifierCode $modifierCode,
        MockInterface $modifierTable
    )
    {
        $modifierTable->shouldReceive('getDifficultyChange')
            ->with($modifierCode)
            ->andReturn($difficultyChange);
    }

    /**
     * @test
     */
    public function I_get_basic_difficulty_change_with_every_parameter()
    {
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            $parameterDifficulties = [];
            foreach (ModifierMutableSpellParameterCode::getPossibleValues() as $mutableParameterName) {
                $parameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, $parameter);
                $changedParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addExpectedAdditionSetter(0, $parameter, $changedParameter);
                $parameterDifficulties[] = $difficultyChange = random_int(-10, 10);
                $this->addAdditionByDifficultyGetter($difficultyChange, $changedParameter);
            }
            $spellTraits = [$this->createSpellTrait(123), [$this->createSpellTrait(456)]];
            $difficultyChangeValue = 123 + 456 + array_sum($parameterDifficulties);
            $difficultyChange = $this->createDifficultyChange($difficultyChangeValue);
            $this->addDifficultyChangeGetter($difficultyChange, $modifierCode, $modifiersTable);
            $modifier = new Modifier($modifierCode, $modifiersTable, [], $spellTraits);
            try {
                self::assertSame($difficultyChange, $modifier->getDifficultyChange());
            } catch (NoMatchingExpectationException $expectationException) {
                self::fail(
                    'Expected difficulty sum ' . $difficultyChangeValue
                    . ': ' . $expectationException->getMessage()
                );
            }
        }
    }

    /**
     * @param int $difficultyChangeValue
     * @return MockInterface|SpellTrait
     */
    private function createSpellTrait(int $difficultyChangeValue)
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
    public function I_can_get_casting_rounds()
    {
        $modifier = new Modifier(
            $modifierCode = ModifierCode::getIt(ModifierCode::COLOR),
            $modifiersTable = $this->createModifiersTable(),
            [],
            []
        );
        $modifiersTable->shouldReceive('getCastingRounds')
            ->with($modifierCode)
            ->andReturn($castingRounds = $this->mockery(CastingRounds::class));
        self::assertSame($castingRounds, $modifier->getCastingRounds());
    }

    /**
     * @test
     */
    public function I_can_get_required_realm_for_modifier()
    {
        $modifier = new Modifier(
            $modifierCode = ModifierCode::getIt(ModifierCode::TRANSPOSITION),
            $modifiersTable = $this->createModifiersTable(),
            [],
            []
        );
        $modifiersTable->shouldReceive('getRealm')
            ->with($modifierCode)
            ->andReturn($realm = $this->mockery(Realm::class));
        self::assertSame($realm, $modifier->getRequiredRealm());
    }

    /**
     * @test
     */
    public function I_can_get_realms_affection()
    {
        $modifiersTable = new ModifiersTable(Tables::getIt());
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifier = new Modifier(
                $modifierCode = ModifierCode::getIt($modifierValue),
                $modifiersTable,
                [],
                []
            );
            self::assertEquals(
                $modifiersTable->getRealmsAffection($modifierCode),
                $modifier->getRealmsAffection()
            );
        }
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameter
     * @expectedExceptionMessageRegExp ~0\.1~
     */
    public function I_can_not_create_it_with_non_integer_addition()
    {
        try {
            new Modifier(
                ModifierCode::getIt(ModifierCode::INVISIBILITY),
                $this->createModifiersTable(),
                [ModifierMutableSpellParameterCode::EPICENTER_SHIFT => 0.0],
                []
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        try {
            $modifiersTable = $this->createModifiersTable();
            $this->addBaseParameterGetter(
                $parameterName = ModifierMutableSpellParameterCode::RADIUS,
                $code = ModifierCode::getIt(ModifierCode::GATE),
                $modifiersTable,
                $this->createExpectedParameter($parameterName)
            );
            new Modifier(
                $code,
                $modifiersTable,
                [$parameterName => '5.000'],
                []
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        new Modifier(
            ModifierCode::getIt(ModifierCode::TRANSPOSITION),
            $this->createModifiersTable(),
            [ModifierMutableSpellParameterCode::GRAFTS => 0.1],
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
            $modifiersTable = $this->createModifiersTable();
            $this->addBaseParameterGetter(
                $parameterName = ModifierMutableSpellParameterCode::ATTACK,
                $modifierCode = ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION),
                $modifiersTable,
                $this->createExpectedParameter($parameterName)
            );
            new Modifier(
                $modifierCode,
                $modifiersTable,
                [$parameterName => 4],
                []
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        $modifiersTable = $this->createModifiersTable();
        $this->addBaseParameterGetter(
            $parameterName = ModifierMutableSpellParameterCode::RESISTANCE,
            $modifierCode = ModifierCode::getIt(ModifierCode::COLOR),
            $modifiersTable,
            null // unused
        );
        new Modifier($modifierCode, $modifiersTable, [$parameterName => 4], []);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     * @expectedExceptionMessageRegExp ~useless~
     */
    public function I_can_not_create_it_with_addition_of_unknown_parameter()
    {
        new Modifier(ModifierCode::getIt(ModifierCode::TRANSPOSITION), $this->createModifiersTable(), ['useless' => 0], []);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidSpellTrait
     * @expectedExceptionMessageRegExp ~DateTime~
     */
    public function I_can_create_it_with_non_spell_trait_as_spell_trait()
    {
        new Modifier(ModifierCode::getIt(ModifierCode::TRANSPOSITION), $this->createModifiersTable(), [], [new \DateTime()]);
    }
}