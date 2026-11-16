<?php

declare(strict_types=1);

namespace G\Dump\Type;

class ArrayType extends AbstractType
{
    public function getPriority(): int
    {
        return 0;
    }

    public function supports(mixed $var): bool
    {
        return is_array($var);
    }

    /**
     * @param array<mixed> $var
     */
    public function dump(mixed $var): string
    {
        $dumper = $this->dumper;
        $theme = $dumper->getTheme();

        $output = [$theme->output(
            type: 'array',
            complement: sprintf('%d', count($var))
        )];

        foreach ($var as $key => $value) {
            $dumper->increaseIndent();
            $output[] = sprintf(
                '%s%s => %s',
                $dumper->getIndent(),
                $theme->output(
                    type: 'key',
                    value: is_string($key) ? "'" . $theme->escape($key) . "'" : (string) $key
                ),
                $dumper->dump($value)
            );
            $dumper->decreaseIndent();
        }

        return implode("\n", $output);
    }
}
