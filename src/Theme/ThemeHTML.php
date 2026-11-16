<?php

declare(strict_types=1);

namespace G\Dump\Theme;

class ThemeHTML implements ThemeInterface
{
    private const string FOOTER = '</pre>';
    private const string TYPE_STRING = 'string';

    private const DEFAULT_STYLE = [
        'pre' => 'padding:0.25rem; overflow:auto',
        'indent' => 'color:lightgrey; font-weight:lighter',
        'type' => 'color:royalblue',
        'complement' => 'color:forestgreen; font-style:italic',
        'value' => 'color:inherit',
    ];

    /** @var array<string, string> */
    protected const STYLE_TYPE = [
        // 'boolean' => '',
        // 'integer' => '',
        // 'double' => '',
        // 'string' => '',
        // 'json' => '',
        // 'json slashed' => '',
        // 'serialized' => '',
        // 'array' => '',
        // 'object' => '',
        // 'exception' => '',
        // 'resource' => '',
        // 'resource (closed)' => '',
        // 'null' => '',
        // 'unknown type' => '',
    ];

    /** @var array<string, string> */
    protected const STYLE_COMPLEMENT = [
        // 'boolean' => '',
        // 'integer' => '',
        // 'double' => '',
        // 'string' => '',
        // 'json' => '',
        // 'json slashed' => '',
        // 'serialized' => '',
        // 'array' => '',
        // 'object' => '',
        // 'resource' => '',
        // 'resource (closed)' => '',
        // 'unknown type' => '',
        // 'trace' => '',
        // 'exception' => '',
        // 'visibility' => '',
        // 'key' => '',
    ];

    /** @var array<string, string> */
    protected const STYLE_VALUE = [
        'boolean' => 'color:royalblue',
        'integer' => 'color:orange',
        'double' => 'color:orange',
        'string' => 'color:tomato',
        'json' => '',
        'json slashed' => '',
        'serialized' => '',
        // 'array' => '',
        // 'object' => '',
        'resource' => 'color:crimson',
        'resource (closed)' => 'color:crimson',
        // 'unknown type' => '',
        'trace' => 'color:crimson',
        'exception' => 'color:crimson',
        'visibility' => 'color:mediumslateblue',
        'key' => 'color:orchid',
        'indent' => 'color:grey',
        'no-select' => 'user-select:none; color:grey; font-size:0.6rem',
    ];

    /** @var array<string, string> */
    protected const STYLE_VISIBILITY = [
        // 'public' => '',
        // 'protected' => '',
        // 'private' => '',
        // 'static' => '',
    ];

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
        return sprintf('<pre style="%s">', self::DEFAULT_STYLE['pre']);
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
            $output .= '(' . $this->getComplement($type, $complement) . ')';
        }

        if ($value !== null) {
            $output .= ' ' . $this->getValue($type, $value);
        }

        return trim($output);
    }

    public function footer(): string
    {
        return self::FOOTER;
    }

    public function indent(int $depth): string
    {
        if ($depth < 1) {
            return '';
        }

        return sprintf(
            '<span style="%s">%s&brvbar;- </span>',
            self::DEFAULT_STYLE['indent'],
            str_repeat('&brvbar;  ', $depth - 1)
        );
    }

    public function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /* --- */

    private function getType(string $type): string
    {
        $themeType = self::pick(self::STYLE_TYPE, $type, 'type');

        return sprintf(
            '<span style="%s">%s</span>',
            $themeType,
            match ($type) {
                'integer' => 'int',
                'double' => 'float',
                default => $type
            }
        );
    }

    private function getComplement(string $type, string $complement): string
    {
        $themeComplement = self::pick(self::STYLE_COMPLEMENT, $type, 'complement');

        return sprintf(
            '<span style="%s">%s</span>',
            $themeComplement,
            $complement
        );
    }

    private function getValue(string $type, string $value): string
    {
        $themeValue = self::pick(self::STYLE_VALUE, $type, 'value');

        if ($type === self::TYPE_STRING) {
            $span = sprintf('<span style="%s">%%s</span>', self::STYLE_VALUE['no-select']);
            $value = strtr($value, [
                "\r\n" => sprintf($span, '\r\n') . "\r\n",
                "\r" => sprintf($span, '\r') . "\r",
                "\n" => sprintf($span, '\n') . "\n",
            ]);
        }

        return sprintf(
            '<span style="%s">%s</span>',
            $themeValue,
            $value
        );
    }

    /**
     * Style of a type, or the default style of the given kind.
     *
     * @param array<string, string> $styles
     * @param 'type'|'complement'|'value' $kind
     */
    private static function pick(array $styles, string $type, string $kind): string
    {
        return $styles[strtolower($type)] ?? self::DEFAULT_STYLE[$kind];
    }
}
