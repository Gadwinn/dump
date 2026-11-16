<?php

declare(strict_types=1);

namespace G\Dump\Theme;

class ThemeText implements ThemeInterface
{
    private const TYPES = [
        'boolean',
        'integer',
        'double',
        'string',
        'json',
        'json slashed',
        'serialized',
        'array',
        'object',
        'exception',
        'resource',
        'resource (closed)',
        'NULL',
        'unknown type',
    ];

    public function header(): string
    {
        return '';
    }

    public function output(
        string $type,
        ?string $complement = null,
        ?string $value = null
    ): string {
        $output = '';

        if (in_array($type, self::TYPES)) {
            $output .= $this->getType($type);
        }

        if ($complement !== null) {
            $output .= '(' . $this->getComplement($complement) . ')';
        }

        if ($value !== null) {
            $output .= ' ' . $this->getValue($value);
        }

        return trim($output);
    }

    public function footer(): string
    {
        return '';
    }

    public function indent(int $depth): string
    {
        return $depth < 1 ? '' : str_repeat('|  ', $depth - 1) . '|- ';
    }

    public function escape(string $text): string
    {
        return $text;
    }

    /* --- */

    private function getType(string $type): string
    {
        return match ($type) {
            'integer' => 'int',
            'double' => 'float',
            default => $type
        };
    }

    private function getComplement(string $complement): string
    {
        return $complement;
    }

    private function getValue(string $value): string
    {
        return $value;
    }
}
