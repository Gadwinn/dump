<?php

declare(strict_types=1);

namespace G\Dump\Type;

class StringType extends AbstractType
{
    private const string CUT_MARK = '[...]';
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
        $max = $this->dumper->getMaxStringLength();
        $shown = $this->dumper->getTheme()->escape($max > 0 && $length > $max ? mb_substr($var, 0, $max) : $var);

        return $this->dumper->getTheme()->output(
            type: gettype($var),
            complement: $length . ($length !== $bytes ? " - size=$bytes" : ''),
            // The length shown in the complement is always the real one
            value: "'" . $shown . ($max > 0 && $length > $max ? self::CUT_MARK : '') . "'"
        );
    }

    // private function humanizeBytes(int $bytes): string
    // {
    //     $units = ['', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    //     $factor = (int) floor(log($bytes, 1024));
    //     return sprintf('%g%s', $bytes / (1024 ** $factor), $units[$factor]);
    // }
}
