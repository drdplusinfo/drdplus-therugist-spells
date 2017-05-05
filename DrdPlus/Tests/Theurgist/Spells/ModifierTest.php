<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\ModifierCode;
use DrdPlus\Theurgist\Codes\ModifierMutableCastingParameterCode;
use DrdPlus\Theurgist\Spells\CastingParameters\Partials\IntegerCastingParameter;
use DrdPlus\Theurgist\Spells\CastingParameters\SpellSpeed;
use DrdPlus\Theurgist\Spells\Modifier;
use DrdPlus\Theurgist\Spells\ModifiersTable;
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
            $modifier = new Modifier($modifierCode, $modifiersTable, []);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, $baseParameter);
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $modifier->$getBaseParameter());

                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
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
        $parameter->shouldReceive('setAddition')
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
            $modifier = new Modifier($modifierCode, $modifiersTable, []);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /*if ($mutableParameterName === ModifierMutableCastingParameterCode::) {
                    continue; // can not be null, skipping
                }*/
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, null);
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertNull($modifier->$getBaseParameter());

                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
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
        $additions = [];
        foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $index => $parameterValue) {
            $additions[$parameterValue] = $index + 1; // 1...x
        }
        foreach (ModifierCode::getPossibleValues() as $modifierValue) {
            $modifierCode = ModifierCode::getIt($modifierValue);
            $modifiersTable = $this->createModifiersTable();
            $baseParameters = [];
            foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                /** like instance of @see SpellSpeed */
                $baseParameter = $this->createExpectedParameter($mutableParameterName);
                $this->addBaseParameterGetter($mutableParameterName, $modifierCode, $modifiersTable, $baseParameter);
                $baseParameters[$mutableParameterName] = $baseParameter;
            }
            $modifier = new Modifier($modifierCode, $modifiersTable, $additions);
            self::assertSame($modifierCode, $modifier->getModifierCode());
            foreach (ModifierMutableCastingParameterCode::getPossibleValues() as $mutableParameterName) {
                $baseParameter = $baseParameters[$mutableParameterName];
                $addition = $additions[$mutableParameterName];
                /** like @see Modifier::getBaseRadius */
                $getBaseParameter = StringTools::assembleGetterForName('base_' . $mutableParameterName);
                self::assertSame($baseParameter, $modifier->$getBaseParameter());
                /** like @see Modifier::getCurrentRadius() */
                $getCurrentParameter = StringTools::assembleGetterForName('current_' . $mutableParameterName);
                $this->addExpectedAdditionSetter(
                    $addition,
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
                self::assertNotSame(0, $addition);
                self::assertSame($addition, $modifier->$getParameterAddition());
            }
        }
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\InvalidValueForModifierParameterAddition
     * @expectedExceptionMessageRegExp ~0\.1~
     */
    public function I_can_not_create_it_with_non_integer_addition()
    {
        try {
            new Modifier(
                ModifierCode::getIt(ModifierCode::INVISIBILITY),
                $this->createModifiersTable(),
                [ModifierMutableCastingParameterCode::EPICENTER_SHIFT => 0.0]
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        try {
            $modifiersTable = $this->createModifiersTable();
            $this->addBaseParameterGetter(
                $parameterName = ModifierMutableCastingParameterCode::RADIUS,
                $code = ModifierCode::getIt(ModifierCode::GATE),
                $modifiersTable,
                $this->createExpectedParameter($parameterName)
            );
            new Modifier(
                $code,
                $modifiersTable,
                [$parameterName => '5.000']
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        new Modifier(
            ModifierCode::getIt(ModifierCode::TRANSPOSITION),
            $this->createModifiersTable(),
            [ModifierMutableCastingParameterCode::GRAFTS => 0.1]
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
                $parameterName = ModifierMutableCastingParameterCode::ATTACK,
                $modifierCode = ModifierCode::getIt(ModifierCode::INTERACTIVE_ILLUSION),
                $modifiersTable,
                $this->createExpectedParameter($parameterName)
            );
            new Modifier(
                $modifierCode,
                $modifiersTable,
                [$parameterName => 4]
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . '; ' . $exception->getTraceAsString());
        }
        $modifiersTable = $this->createModifiersTable();
        $this->addBaseParameterGetter(
            $parameterName = ModifierMutableCastingParameterCode::RESISTANCE,
            $modifierCode = ModifierCode::getIt(ModifierCode::COLOR),
            $modifiersTable,
            null // unused
        );
        new Modifier($modifierCode, $modifiersTable, [$parameterName => 4]);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Spells\Exceptions\UnknownModifierParameter
     * @expectedExceptionMessageRegExp ~useless~
     */
    public function I_can_not_create_it_with_addition_of_unknown_parameter()
    {
        new Modifier(ModifierCode::getIt(ModifierCode::TRANSPOSITION), $this->createModifiersTable(), ['useless' => 0]);
    }

}