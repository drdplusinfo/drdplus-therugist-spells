<?php
namespace DrdPlus\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\SpellParameters\DifficultyChange;
use DrdPlus\Theurgist\Spells\SpellParameters\Trap;
use Granam\Strict\Object\StrictObject;

class SpellTrait extends StrictObject
{
    /** @var SpellTraitCode */
    private $spellTraitCode;
    /** @var SpellTraitsTable */
    private $spellTraitsTable;
    /** @var int */
    private $trapPropertyChange;

    /**
     * @param SpellTraitCode $spellTraitCode
     * @param SpellTraitsTable $spellTraitsTable
     * @param int $spellTraitTrapPropertyChange
     */
    public function __construct(
        SpellTraitCode $spellTraitCode,
        SpellTraitsTable $spellTraitsTable,
        int $spellTraitTrapPropertyChange = 0
    )
    {
        $this->spellTraitCode = $spellTraitCode;
        $this->spellTraitsTable = $spellTraitsTable;
        $this->trapPropertyChange = $spellTraitTrapPropertyChange;
    }

    public function getDifficultyChange(): DifficultyChange
    {
        $difficultyChange = $this->spellTraitsTable->getDifficultyChange($this->getSpellTraitCode());
        $trap = $this->getCurrentTrap();
        if (!$trap) {
            return $difficultyChange;
        }

        return $difficultyChange->add($trap->getAdditionByDifficulty()->getCurrentDifficultyIncrement());
    }

    /**
     * @return SpellTraitCode
     */
    public function getSpellTraitCode(): SpellTraitCode
    {
        return $this->spellTraitCode;
    }

    /**
     * @return Trap|null
     */
    public function getBaseTrap()
    {
        return $this->spellTraitsTable->getTrap($this->getSpellTraitCode());
    }

    /**
     * @return Trap|null
     */
    public function getCurrentTrap()
    {
        $trap = $this->getBaseTrap();
        if (!$trap) {
            return null;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $trap->getWithAddition($this->trapPropertyChange);
    }

    public function __toString()
    {
        return (string)$this->getSpellTraitCode()->getValue();
    }
}