<?php
namespace DrdPlus\Theurgist\Spells\CastingParameters;

use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\SpellTraitsTable;
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
     * @var SpellTraitCode
     */
    private $traitCode;

    /**
     * @param string $spellTraitAnnotation in format 'trait name' or 'trait name=number'
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidValueForSpellTraitDifficultyChange
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\UnexpectedFormatOfSpellTrait
     */
    public function __construct(string $spellTraitAnnotation)
    {
        $parts = $this->parseParts($spellTraitAnnotation);
        if (count($parts) === 1) {
            $this->traitCode = SpellTraitCode::getIt($parts[0]);
            $this->difficultyChange = 1;
        } else if (count($parts) === 2) {
            $this->traitCode = SpellTraitCode::getIt($parts[0]);
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
     * @throws \DrdPlus\Theurgist\Spells\CastingParameters\Exceptions\InvalidValueForSpellTraitDifficultyChange
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
        if (count($parts) > 2) {
            return [];
        }

        return $parts;
    }

    /**
     * @return int
     */
    public function getDifficultyChange(): int
    {
        return $this->difficultyChange;
    }

    /**
     * @return SpellTraitCode
     */
    public function getSpellTraitCode(): SpellTraitCode
    {
        return $this->traitCode;
    }

    /**
     * @param SpellTraitsTable $spellTraitsTable
     * @return Trap|null
     */
    public function getTrap(SpellTraitsTable $spellTraitsTable)
    {
        return $spellTraitsTable->getTrap($this->getSpellTraitCode());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDifficultyChange() . '=>' . $this->getSpellTraitCode();
    }
}