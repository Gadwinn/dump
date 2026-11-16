<?php

declare(strict_types=1);

namespace G\Dump\Type;

interface TypeInterface
{
    /**
     * Get the type priority.
     *
     * Higher number means higher priority
     */
    public function getPriority(): int;

    /**
     * Check if the type is supported
     *
     * @param mixed $var The variable to check
     *
     * @return bool
     */
    public function supports(mixed $var): bool;

    /**
     * Dump the variable
     *
     * @param mixed $var The variable to dump
     *
     * @return string
     */
    public function dump(mixed $var): string;
}
