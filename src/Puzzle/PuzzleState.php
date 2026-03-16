<?php

declare(strict_types=1);

namespace App\Puzzle;

use InvalidArgumentException;

final class PuzzleState
{
    public const int DIAL_COUNT = 7;
    public const int POSITION_COUNT = 4;

    /**
     * @var list<int>
     */
    private array $positions;

    /**
     * @param list<int> $positions
     */
    public function __construct(array $positions)
    {
        if (count($positions) !== self::DIAL_COUNT) {
            throw new InvalidArgumentException(sprintf('Expected %d dial positions.', self::DIAL_COUNT));
        }

        foreach ($positions as $position) {
            if (!is_int($position) || $position < 0 || $position >= self::POSITION_COUNT) {
                throw new InvalidArgumentException('Dial positions must be integers between 0 and 3.');
            }
        }

        $this->positions = array_values($positions);
    }

    public static function initial(): self
    {
        return new self([2, 0, 1, 3, 2, 1, 3]);
    }

    public static function solved(): self
    {
        return new self(array_fill(0, self::DIAL_COUNT, 0));
    }

    /**
     * @return list<int>
     */
    public function positions(): array
    {
        return $this->positions;
    }

    public function rotate(int $index, PuzzleMode $mode): self
    {
        if ($index < 0 || $index >= self::DIAL_COUNT) {
            throw new InvalidArgumentException('Dial index is out of range.');
        }

        $next = $this->positions;
        $lastIndex = $mode === PuzzleMode::Joint ? self::DIAL_COUNT - 1 : $index;

        for ($i = $index; $i <= $lastIndex; $i++) {
            $next[$i] = ($next[$i] + 1) % self::POSITION_COUNT;
        }

        return new self($next);
    }

    public function isSolved(): bool
    {
        foreach ($this->positions as $position) {
            if ($position !== 0) {
                return false;
            }
        }

        return true;
    }

    public function key(): string
    {
        return implode('', array_map(static fn (int $position): string => (string) $position, $this->positions));
    }
}
