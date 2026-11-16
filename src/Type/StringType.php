<?php

declare(strict_types=1);

namespace G\Dump\Type;

class StringType extends AbstractType
{
    private const int PRIORITY = 10;

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        return is_string($var);
    }

    /**
     * @param string $var
     */
    public function dump(mixed $var): string
    {
        $length = mb_strlen($var);
        $bytes = strlen($var);

        return $this->dumper->getTheme()->output(
            type: gettype($var),
            complement: $length . ($length !== $bytes ? " - size=$bytes" : ''),
            value: "'" . $this->dumper->getTheme()->escape($var) . "'"
        );
    }

    // private function humanizeBytes(int $bytes): string
    // {
    //     $units = ['', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    //     $factor = (int) floor(log($bytes, 1024));
    //     return sprintf('%g%s', $bytes / (1024 ** $factor), $units[$factor]);
    // }
}
