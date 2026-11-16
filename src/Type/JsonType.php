<?php

declare(strict_types=1);

namespace G\Dump\Type;

class JsonType extends AbstractType
{
    private const int PRIORITY = 15;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return is_string($var) && preg_match('/^[\[{]/', $var) && json_validate($var);
    }

    /**
     * @param string $var
     */
    public function dump(mixed $var): string
    {
        return $this->dumper->getTheme()->output(
            type: 'json',
            value: $this->dumper->dump(json_decode($var, true))
        );
    }
}
