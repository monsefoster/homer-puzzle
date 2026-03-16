<?php

declare(strict_types=1);

namespace App\Puzzle;

final readonly class PuzzleAnalysis
{
    /**
     * @param list<int> $firstSolution
     */
    public function __construct(
        public int $minimalMoves,
        public int $solutionCount,
        public array $firstSolution,
    ) {
    }
}
