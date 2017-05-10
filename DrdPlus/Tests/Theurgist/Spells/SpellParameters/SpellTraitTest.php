<?php
namespace DrdPlus\Tests\Theurgist\Spells;

use DrdPlus\Theurgist\Codes\SpellTraitCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Trap;
use DrdPlus\Theurgist\Spells\SpellTrait;
use DrdPlus\Theurgist\Spells\SpellTraitsTable;
use Granam\Tests\Tools\TestWithMockery;

class SpellTraitTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $active = new SpellTrait(
            SpellTraitCode::getIt(SpellTraitCode::ACTIVE),
            $this->createSpellTraitsTableShell()
        );
        self::assertSame(SpellTraitCode::getIt(SpellTraitCode::ACTIVE), $active->getSpellTraitCode());
        self::assertSame('active', (string)$active);
    }

    /**
     * @return \Mockery\MockInterface|SpellTraitsTable
     */
    private function createSpellTraitsTableShell()
    {
        return $this->mockery(SpellTraitsTable::class);
    }

    /**
     * @test
     */
    public function I_can_get_trap()
    {
        foreach (SpellTraitCode::getPossibleValues() as $spellTraitValue) {
            $spellTraitCode = SpellTraitCode::getIt($spellTraitValue);
            $spellTraitsTable = $this->createSpellTraitsTable($spellTraitCode, $trap = $this->createTrap(0));
            $spellTraitWithoutTrapChange = new SpellTrait($spellTraitCode, $spellTraitsTable, 0);
            self::assertSame($trap, $spellTraitWithoutTrapChange->getBaseTrap());
            self::assertSame($trap, $spellTraitWithoutTrapChange->getCurrentTrap());

            $spellTraitsTable = $this->createSpellTraitsTable(
                $spellTraitCode,
                $baseTrap = $this->createTrap(10, $changedTrap = $this->createTrapShell())
            );
            $spellTraitWithTrapChange = new SpellTrait($spellTraitCode, $spellTraitsTable, 10);
            self::assertSame($baseTrap, $spellTraitWithTrapChange->getBaseTrap());
            self::assertEquals($changedTrap, $spellTraitWithTrapChange->getCurrentTrap());
        }
    }

    /**
     * @param SpellTraitCode $spellTraitCode
     * @param $trap
     * @return \Mockery\MockInterface|SpellTraitsTable
     */
    private function createSpellTraitsTable(SpellTraitCode $spellTraitCode, $trap)
    {
        $spellTraitsTable = $this->mockery(SpellTraitsTable::class);
        $spellTraitsTable->shouldReceive('getTrap')
            ->with($spellTraitCode)
            ->andReturn($trap);

        return $spellTraitsTable;
    }

    /**
     * @param int $expectedTrapChange
     * @param Trap $changedTrap
     * @return \Mockery\MockInterface|Trap
     */
    private function createTrap(int $expectedTrapChange, Trap $changedTrap = null)
    {
        $trap = $this->mockery(Trap::class);
        $trap->shouldReceive('getWithAddition')
            ->with($expectedTrapChange)
            ->andReturn($changedTrap ?? $trap);

        return $trap;
    }

    /**
     * @return \Mockery\MockInterface|Trap
     */
    private function createTrapShell()
    {
        return $this->mockery(Trap::class);
    }
}