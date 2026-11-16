<?php

declare(strict_types=1);

namespace G\Dump\Type;

class NullType extends AbstractType
{
    private const int PRIORITY = 20;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return $var === null;
    }

    /**
     * @param null $var
     */
    public function dump(mixed $var): string
    {
        return $this->dumper->getTheme()->output(
            type: gettype($var)
        );
    }
}
