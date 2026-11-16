<?php

declare(strict_types=1);

namespace G\Dump;

use G\Dump\Theme\ThemeInterface;

interface DumperInterface
{
    /**
     * Get theme
     *
     * @return ThemeInterface
     */
    public function getTheme(): ThemeInterface;

    /**
     * Get indent string
     *
     * @return string
     */
    public function getIndent(): string;

    /**
     * Increase indent
     *
     * @return void
     */
    public function increaseIndent(): void;

    /**
     * Decrease indent
     *
     * @return void
     */
    public function decreaseIndent(): void;

    /**
     * Maximum number of characters shown for a string (0 = no limit)
     *
     * @return int
     */
    public function getMaxStringLength(): int;

    /**
     * Whether the methods of the objects are listed
     *
     * @return bool
     */
    public function showsMethods(): bool;

    /**
     * Dump variables
     *
     * @param mixed ...$vars
     *
     * @return string
     */
    public function dump(mixed ...$vars): string;
}
