<?php

declare(strict_types=1);

namespace G\Dump\Theme;

interface ThemeInterface
{
    /**
     * Opening of a dump.
     */
    public function header(): string;

    public function output(
        string $type,
        ?string $complement = null,
        ?string $value = null
    ): string;

    public function footer(): string;

    /**
     * Prefix of a nested line at the given depth (0 = no prefix).
     */
    public function indent(int $depth): string;

    /**
     * Makes a raw value (string content, key, class name...) safe to print with this theme.
     */
    public function escape(string $text): string;
}
