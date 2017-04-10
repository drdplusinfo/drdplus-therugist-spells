<?php
namespace DrdPlus\Tests\Theurgist\Formulas\CastingParameters;

use DrdPlus\Tests\Theurgist\Formulas\AbstractTheurgistTableTest;
use DrdPlus\Theurgist\Codes\TraitCode;

class SpellTraitsTableTest extends AbstractTheurgistTableTest
{

    /**
     * @test
     */
    public function I_can_get_every_obligatory_parameter()
    {
        $obligatoryParameters = ['difficulty_change'];
        foreach ($obligatoryParameters as $obligatoryParameter) {
            $this->I_can_get_obligatory_parameter($obligatoryParameter, TraitCode::class);
        }
    }

    /**
     * @test
     */
    public function I_can_get_every_optional_parameter()
    {
        $optionalParameters = ['trap'];
        foreach ($optionalParameters as $optionalParameter) {
            $this->I_can_get_optional_parameter($optionalParameter, TraitCode::class);
        }
    }

}