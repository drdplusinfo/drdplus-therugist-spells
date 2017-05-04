<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use Granam\Tests\ExceptionsHierarchy\Exceptions\AbstractExceptionsHierarchyTest;

class TheurgistExceptionsHierarchyTest extends AbstractExceptionsHierarchyTest
{
    protected function getTestedNamespace()
    {
        return $this->getRootNamespace();
    }

    protected function getRootNamespace()
    {
        return str_replace('\Tests', '', __NAMESPACE__);
    }

}