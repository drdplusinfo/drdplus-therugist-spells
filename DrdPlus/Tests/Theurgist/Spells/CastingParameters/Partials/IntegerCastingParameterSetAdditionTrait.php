<?php
namespace DrdPlus\Tests\Theurgist\Spells\CastingParameters\Partials;

/**
 * @method static getSutClass
 * @method static assertSame($expected, $current, $message = '')
 */
trait IntegerCastingParameterSetAdditionTrait
{

    /**
     * @test
     */
    public function I_get_whispered_current_class_as_return_value_of_set_addition()
    {
        $reflectionClass = new \ReflectionClass(self::getSutClass());
        $classBaseName = preg_replace('~^.*[\\\](\w+)$~', '$1', self::getSutClass());
        $add = $reflectionClass->getMethod('setAddition');
        if (strpos($add->getDocComment(), $classBaseName) !== false) {
            self::assertSame($phpDoc = <<<PHPDOC
/**
 * @param int|float|NumberInterface \$additionValue
 * @return {$classBaseName}
 * @throws \Granam\Integer\Tools\Exceptions\Exception
 */
PHPDOC
                , preg_replace('~ {2,}~', ' ', $add->getDocComment()),
                "Expected:\n$phpDoc\nfor method 'setAddition'"
            );
        } else {
            self::assertSame($phpDoc = <<<PHPDOC
/**
 * @method {$classBaseName} setAddition(\$additionValue)
 */
PHPDOC
                , $reflectionClass->getDocComment(),
                "Expected:\n$phpDoc"
            );
        }
    }
}