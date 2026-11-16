<?php

declare(strict_types=1);

namespace G\Dump\Tests;

/**
 * Fixture with constants and methods of every kind.
 */
class WithMembers
{
    public const string LABEL = 'label';
    private const int LIMIT = 3;

    public string $name = 'x';

    public function rename(string $name, ?int $times = null, string ...$more): static
    {
        $this->name = str_repeat($name, $times ?? self::LIMIT) . implode('', $more);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    final protected static function make(array &$options = []): int
    {
        $options = [];

        return 0;
    }

    private function secret(bool $flag = true, string $mode = 'fast'): void
    {
    }
}
