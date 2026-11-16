<?php

declare(strict_types=1);

namespace G\Dump\Type;

class JsonSlashedType extends AbstractType
{
    private const int PRIORITY = 14;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return is_string($var) && preg_match('/^[\[{]/', $var) && json_validate(stripslashes($var));
    }

    /**
     * @param string $var
     */
    public function dump(mixed $var): string
    {
        return $this->dumper->getTheme()->output(
            type: 'json slashed',
            value: $this->dumper->dump(json_decode(stripslashes($var), true))
        );
    }
}
