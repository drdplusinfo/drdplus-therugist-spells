<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Formulas\CastingParameters\CastingParameter;
use Granam\Tests\Tools\TestWithMockery;

final class CastingParameterTest extends TestWithMockery
{
    /**
     * @test
     * @expectedException \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\MissingValueForAdditionByRealm
     * @expectedExceptionMessageRegExp ~123~
     */
    public function I_can_not_create_it_with_invalid_points_to_annotation()
    {
        $reflectionMethod = new \ReflectionMethod(CastingParameter::class, '__construct');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->mockery(CastingParameter::class), ['foo'], 123);
    }
}