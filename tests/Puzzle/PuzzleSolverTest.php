<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

use App\Puzzle\PuzzleMode;
use App\Puzzle\PuzzleSolver;
use App\Puzzle\PuzzleState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PuzzleSolverTest extends TestCase
{
    #[Test]
    public function it_reports_the_solved_state_immediately(): void
    {
        $analysis = (new PuzzleSolver())->analyze(PuzzleState::solved(), PuzzleMode::Joint);

        self::assertSame(0, $analysis->minimalMoves);
        self::assertSame(1, $analysis->solutionCount);
        self::assertSame([], $analysis->firstSolution);
    }

    #[Test]
    public function it_solves_the_initial_state_in_individual_mode(): void
    {
        $analysis = (new PuzzleSolver())->analyze(PuzzleState::initial(), PuzzleMode::Individual);

        self::assertSame(12, $analysis->minimalMoves);
        self::assertGreaterThan(0, $analysis->solutionCount);
        self::assertCount(12, $analysis->firstSolution);
        self::assertTrue($this->applyMoves(PuzzleState::initial(), $analysis->firstSolution, PuzzleMode::Individual)->isSolved());
    }

    #[Test]
    public function it_returns_a_valid_shortest_solution_in_joint_mode(): void
    {
        $analysis = (new PuzzleSolver())->analyze(PuzzleState::initial(), PuzzleMode::Joint);

        self::assertGreaterThan(0, $analysis->minimalMoves);
        self::assertGreaterThan(0, $analysis->solutionCount);
        self::assertCount($analysis->minimalMoves, $analysis->firstSolution);
        self::assertTrue($this->applyMoves(PuzzleState::initial(), $analysis->firstSolution, PuzzleMode::Joint)->isSolved());
    }

    /**
     * @param list<int> $moves
     */
    private function applyMoves(PuzzleState $state, array $moves, PuzzleMode $mode): PuzzleState
    {
        foreach ($moves as $move) {
            $state = $state->rotate($move, $mode);
        }

        return $state;
    }
}
