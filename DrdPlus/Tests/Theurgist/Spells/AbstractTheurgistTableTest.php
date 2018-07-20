<?php
declare(strict_types = 1);

namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Tables\Partials\AbstractTable;
use DrdPlus\Theurgist\Codes\AbstractTheurgistCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Attack;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;

abstract class AbstractTheurgistTableTest extends TestWithMockery
{
    /**
     * @param string $profile
     * @return string
     */
    protected function reverseProfileGender(string $profile): string
    {
        $oppositeProfile = str_replace('venus', 'mars', $profile, $countOfReplaced);
        if ($countOfReplaced === 1) {
            return $oppositeProfile;
        }
        $oppositeProfile = str_replace('mars', 'venus', $profile, $countOfReplaced);
        self::assertSame(1, $countOfReplaced);

        return $oppositeProfile;
    }

    /**
     * @param AbstractTable $table
     * @param string $formulaName
     * @param string $parameterName
     * @return mixed
     */
    protected function getValueFromTable(AbstractTable $table, string $formulaName, string $parameterName)
    {
        return $table->getIndexedValues()[$formulaName][$parameterName];
    }

    /**
     * @param string $mandatoryParameter
     * @param string|AbstractTheurgistCode $codeClass
     */
    protected function I_can_get_mandatory_parameter(string $mandatoryParameter, string $codeClass)
    {
        $getMandatoryParameter = StringTools::assembleGetterForName($mandatoryParameter);
        $parameterClass = $this->assembleParameterClassName($mandatoryParameter);
        $sutClass = self::getSutClass();
        $sut = new $sutClass();
        foreach ($codeClass::getPossibleValues() as $codeValue) {
            $expectedParameterValue = $this->getValueFromTable($sut, $codeValue, $mandatoryParameter);
            $parameterObject = $sut->$getMandatoryParameter($codeClass::getIt($codeValue));
            $expectedParameterObject = new $parameterClass($expectedParameterValue);
            self::assertEquals($expectedParameterObject, $parameterObject);
        }
    }

    /**
     * @param string $parameter
     * @return string
     */
    protected function assembleParameterClassName(string $parameter): string
    {
        $basename = implode(array_map(
            function (string $parameterPart) {
                return ucfirst($parameterPart);
            },
            explode('_', $parameter)
        ));

        $namespace = (new \ReflectionClass(Attack::class))->getNamespaceName();

        return $namespace . '\\' . $basename;
    }

    /**
     * @param string $optionalParameter
     * @param string|AbstractTheurgistCode $codeClass
     */
    protected function I_can_get_optional_parameter(string $optionalParameter, string $codeClass)
    {
        $getOptionalParameter = StringTools::assembleGetterForName($optionalParameter);
        $parameterClass = $this->assembleParameterClassName($optionalParameter);
        $sutClass = self::getSutClass();
        $sut = new $sutClass();
        foreach ($codeClass::getPossibleValues() as $codeValue) {
            $expectedParameterValue = $this->getValueFromTable($sut, $codeValue, $optionalParameter);
            $parameterObject = $sut->$getOptionalParameter($codeClass::getIt($codeValue));
            $expectedParameterObject = count($expectedParameterValue) !== 0
                ? new $parameterClass($expectedParameterValue)
                : null;
            self::assertEquals($expectedParameterObject, $parameterObject);
        }
    }
}