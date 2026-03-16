<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

use App\Puzzle\PuzzleMode;
use App\Puzzle\PuzzleState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PuzzleStateTest extends TestCase
{
    #[Test]
    public function it_uses_the_legacy_initial_layout(): void
    {
        self::assertSame([2, 0, 1, 3, 2, 1, 3], PuzzleState::initial()->positions());
    }

    #[Test]
    public function it_rotates_only_the_selected_dial_in_individual_mode(): void
    {
        $next = PuzzleState::initial()->rotate(2, PuzzleMode::Individual);

        self::assertSame([2, 0, 2, 3, 2, 1, 3], $next->positions());
    }

    #[Test]
    public function it_rotates_the_clicked_dial_and_everything_to_the_right_in_joint_mode(): void
    {
        $next = PuzzleState::initial()->rotate(2, PuzzleMode::Joint);

        self::assertSame([2, 0, 2, 0, 3, 2, 0], $next->positions());
    }
}
