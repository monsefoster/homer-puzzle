<?php

declare(strict_types=1);

namespace App\Puzzle;

enum PuzzleMode: string
{
    case Joint = 'joint';
    case Individual = 'individual';

    public function label(): string
    {
        return match ($this) {
            self::Joint => 'Joint',
            self::Individual => 'Individual',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Joint => 'Rotate the clicked dial and every dial to its right.',
            self::Individual => 'Rotate only the clicked dial.',
        };
    }
}
