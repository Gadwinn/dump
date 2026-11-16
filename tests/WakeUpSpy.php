<?php

declare(strict_types=1);

namespace G\Dump\Tests;

/**
 * Records whether unserialize() instantiated it.
 */
final class WakeUpSpy
{
    public static bool $woken = false;

    public string $value = 'x';

    public function __wakeup(): void
    {
        self::$woken = true;
    }
}
