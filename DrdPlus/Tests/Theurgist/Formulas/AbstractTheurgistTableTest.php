<?php
namespace DrdPlus\Tests\Theurgist\Formulas;

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
}