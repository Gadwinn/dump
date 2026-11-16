<?php

declare(strict_types=1);

namespace G\Dump\Type;

class ResourceType extends AbstractType
{
    private const int PRIORITY = 5;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return str_starts_with(gettype($var), 'resource');
    }

    /**
     * @param resource $var
     */
    public function dump(mixed $var): string
    {
        return $this->dumper->getTheme()->output(
            type: gettype($var),
            complement: sprintf('#%d', get_resource_id($var)),
            value: is_resource($var)
                ? "'" . $this->dumper->getTheme()->escape(get_resource_type($var)) . "'"
                : null
        );
    }
}
