<?php

namespace App\Support;

/**
 * The icon + colour avatars a user can pick on the Account page. Must match
 * FrontEnd/src/utils/avatarIcons.js (keys only; the frontend owns the look).
 */
class AvatarIcon
{
    public const ICONS = [
        'leaf', 'recycle', 'tree', 'globe', 'sun', 'drop',
        'plant', 'bicycle', 'fish', 'cat', 'heart', 'butterfly',
    ];

    public const COLOURS = ['green', 'blue', 'purple', 'orange', 'teal', 'pink'];

    /** Validation regex for "<icon>:<colour>". */
    public static function pattern(): string
    {
        return '/^(' . implode('|', self::ICONS) . '):(' . implode('|', self::COLOURS) . ')$/';
    }
}
