<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Dispatcher = 'dispatcher';
    case Master = 'master';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}