<?php

declare(strict_types=1);

namespace G\Dump\Type;

use ReflectionObject;
use Throwable;

class ThrowableType extends AbstractType
{
    private const int PRIORITY = 5;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return $var instanceof Throwable;
    }

    /**
     * @param Throwable $var
     */
    public function dump(mixed $var): string
    {
        $dumper = $this->dumper;
        $theme = $dumper->getTheme();
        $output = [];

        $reflection = new ReflectionObject($var);
        $head = sprintf(
            "%s #%s '%s' in '%s:%s'",
            $theme->output(type: 'exception', complement: $theme->escape($reflection->getName())),
            $theme->output(type: 'trace', value: (string) $var->getcode()),
            $theme->output(type: 'trace', value: $theme->escape($var->getMessage())),
            $theme->output(type: 'trace', value: $theme->escape($var->getFile())),
            $theme->output(type: 'trace', value: (string) $var->getLine()),
        );

        $dumper->increaseIndent();

        // Add trace if available
        if ($trace = $var->getTrace()) {
            $output[] = sprintf(
                '%s%s = %s',
                $dumper->getIndent(),
                $theme->output(type: 'key', value: 'trace'),
                $dumper->dump($trace)
            );
        }

        // Add previous exception if available
        if ($previous = $var->getPrevious()) {
            $output[] = sprintf(
                '%s%s = %s',
                $dumper->getIndent(),
                $theme->output(type: 'key', value: 'previous'),
                $dumper->dump($previous)
            );
        }

        $dumper->decreaseIndent();

        return $theme->nest($head, $output);
    }
}
