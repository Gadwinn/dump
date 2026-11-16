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
     * Joins an element with the lines of its children (array items, object members...).
     *
     * A theme able to fold may let the reader hide the children, and start with
     * them hidden when $collapsed is true.
     *
     * @param string[] $children
     */
    public function nest(string $head, array $children, bool $collapsed = false): string;

    /**
     * Prefix of a nested line at the given depth (0 = no prefix).
     */
    public function indent(int $depth): string;

    /**
     * Makes a raw value (string content, key, class name...) safe to print with this theme.
     */
    public function escape(string $text): string;
}
