<?php

namespace App\Enums;

class ClassType
{
    public const ONLINE  = 'online';
    public const OFFLINE = 'offline';

    public static function all(): array
    {
        return [
            self::ONLINE,
            self::OFFLINE,
        ];
    }
}

