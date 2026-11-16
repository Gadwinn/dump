<?php

declare(strict_types=1);

namespace G\Dump\Type;

class DoubleType extends AbstractType
{
    private const int PRIORITY = 10;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return is_float($var);
    }

    /**
     * @param float $var
     */
    public function dump(mixed $var): string
    {
        return $this->dumper->getTheme()->output(
            type: gettype($var),
            value: (string) $var
        );
    }
}
