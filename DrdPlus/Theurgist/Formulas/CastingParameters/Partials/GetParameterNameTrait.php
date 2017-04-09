<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters\Partials;

use Granam\String\StringTools;

trait GetParameterNameTrait
{

    /**
     * @return string
     */
    protected function getParameterName(): string
    {
        $snakeCaseBaseName = StringTools::camelCaseToSnakeCasedBasename(static::class);

        return str_replace('_', ' ', $snakeCaseBaseName);
    }
}