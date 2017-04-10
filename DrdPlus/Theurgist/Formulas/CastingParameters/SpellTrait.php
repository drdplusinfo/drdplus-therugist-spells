<?php
namespace DrdPlus\Theurgist\Formulas\CastingParameters;

use DrdPlus\Theurgist\Codes\TraitCode;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

class SpellTrait extends StrictObject
{
    /**
     * @var int
     */
    private $difficultyChange;
    /**
     * @var TraitCode
     */
    private $traitCode;

    /**
     * @param string $spellTraitAnnotation in format 'trait name' or 'trait name=number'
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForSpellTraitDifficultyChange
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\UnexpectedFormatOfSpellTrait
     */
    public function __construct(string $spellTraitAnnotation)
    {
        $parts = $this->parseParts($spellTraitAnnotation);
        if (count($parts) === 1) {
            $this->traitCode = TraitCode::getIt($parts[0]);
            $this->difficultyChange = 1;
        } else if (count($parts) === 2) {
            $this->traitCode = TraitCode::getIt($parts[0]);
            $this->difficultyChange = $this->sanitizeDifficultyChange($parts[1]);
        } else {
            throw new Exceptions\UnexpectedFormatOfSpellTrait(
                "Expected format of spell trait annotation is 'trait name' or 'trait name=number', got "
                . ValueDescriber::describe($spellTraitAnnotation)
            );
        }
    }

    /**
     * @param string $difficultyChange
     * @return int
     * @throws \DrdPlus\Theurgist\Formulas\CastingParameters\Exceptions\InvalidValueForSpellTraitDifficultyChange
     */
    private function sanitizeDifficultyChange(string $difficultyChange): int
    {
        try {
            return ToInteger::toInteger($difficultyChange);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $exception) {
            throw new Exceptions\InvalidValueForSpellTraitDifficultyChange(
                'Expected integer, got ' . ValueDescriber::describe($difficultyChange)
            );
        }
    }

    /**
     * @param string $additionByRealmNotation
     * @return array|string[]
     */
    private function parseParts(string $additionByRealmNotation): array
    {
        $parts = array_map(
            function (string $part) {
                return trim($part);
            },
            explode('=', $additionByRealmNotation)
        );

        return array_filter(
            $parts,
            function (string $part) {
                return $part !== '';
            }
        );
    }

    /**
     * @return int
     */
    public function getDifficultyChange(): int
    {
        return $this->difficultyChange;
    }

    /**
     * @return TraitCode
     */
    public function getTraitCode(): TraitCode
    {
        return $this->traitCode;
    }
}