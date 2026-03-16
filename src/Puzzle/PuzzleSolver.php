<?php

declare(strict_types=1);

namespace App\Puzzle;

use LogicException;
use SplQueue;

final class PuzzleSolver
{
    public function analyze(PuzzleState $state, PuzzleMode $mode): PuzzleAnalysis
    {
        if ($state->isSolved()) {
            return new PuzzleAnalysis(0, 1, []);
        }

        $goalKey = PuzzleState::solved()->key();
        $startKey = $state->key();

        $distances = [$startKey => 0];
        $pathCounts = [$startKey => 1];
        $firstPaths = [$startKey => []];
        $goalDistance = null;

        $queue = new SplQueue();
        $queue->enqueue($state);

        while (!$queue->isEmpty()) {
            /** @var PuzzleState $current */
            $current = $queue->dequeue();
            $currentKey = $current->key();
            $currentDepth = $distances[$currentKey];

            if ($goalDistance !== null && $currentDepth >= $goalDistance) {
                continue;
            }

            for ($index = 0; $index < PuzzleState::DIAL_COUNT; $index++) {
                $next = $current->rotate($index, $mode);
                $nextKey = $next->key();
                $nextDepth = $currentDepth + 1;

                if (!array_key_exists($nextKey, $distances)) {
                    $distances[$nextKey] = $nextDepth;
                    $pathCounts[$nextKey] = 0;
                    $firstPaths[$nextKey] = [...$firstPaths[$currentKey], $index];
                    $queue->enqueue($next);
                }

                if ($distances[$nextKey] !== $nextDepth) {
                    continue;
                }

                $pathCounts[$nextKey] += $pathCounts[$currentKey];

                if ($nextKey === $goalKey && $goalDistance === null) {
                    $goalDistance = $nextDepth;
                }
            }
        }

        if ($goalDistance === null) {
            throw new LogicException('No solution found for the current puzzle state.');
        }

        return new PuzzleAnalysis(
            $goalDistance,
            $pathCounts[$goalKey],
            $firstPaths[$goalKey],
        );
    }
}
