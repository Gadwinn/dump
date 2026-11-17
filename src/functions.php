<?php

declare(strict_types=1);

use G\Dump\Dumper;
use G\Dump\Theme\{ThemeHTML, ThemeInterface, ThemeText};

if (!function_exists('gdumpOptions')) {
    /**
     * Configure the dumper shared by gdump(), egdump() and dgdump(), and return it.
     *
     * A theme given here replaces the current one; null keeps it. The text theme
     * is used on the command line, the HTML theme everywhere else.
     *
     * @param ThemeInterface|null $themeHtml Theme used in a web context
     * @param ThemeInterface|null $themeCli  Theme used on the command line
     * @param int|null            $maxDepth  Nesting level after which arrays and objects are not expanded
     * @param int|null            $maxStringLength Characters after which a string is cut (0 = no limit)
     * @param bool|null           $showMethods     List the methods of the objects
     */
    function gdumpOptions(
        ?ThemeInterface $themeHtml = null,
        ?ThemeInterface $themeCli = null,
        ?int $maxDepth = null,
        ?int $maxStringLength = null,
        ?bool $showMethods = null
    ): Dumper {
        /** @var ThemeInterface|null $html */
        static $html = null;
        /** @var ThemeInterface|null $cli */
        static $cli = null;
        /** @var int $depth */
        static $depth = Dumper::DEFAULT_MAX_DEPTH;
        /** @var int $stringLength */
        static $stringLength = 0;
        /** @var bool $methods */
        static $methods = true;
        /** @var Dumper|null $dumper */
        static $dumper = null;

        $changed = $themeHtml !== null || $themeCli !== null || $maxDepth !== null
            || $maxStringLength !== null || $showMethods !== null;

        if ($dumper === null || $changed) {
            $html = $themeHtml ?? $html ?? new ThemeHTML();
            $cli = $themeCli ?? $cli ?? new ThemeText();
            $depth = $maxDepth ?? $depth;
            $stringLength = $maxStringLength ?? $stringLength;
            $methods = $showMethods ?? $methods;
            $dumper = new Dumper(
                theme: PHP_SAPI === 'cli' ? $cli : $html,
                maxDepth: $depth,
                maxStringLength: $stringLength,
                showMethods: $methods,
            );
        }

        return $dumper;
    }
}

if (!function_exists('gdump')) {
    /**
     * Dump the passed variables and return the result.
     *
     * @param mixed ...$vars
     *
     * @return string
     */
    function gdump(mixed ...$vars): string
    {
        // The trace line must point to the caller of egdump()/dgdump(), not to these helpers
        $calledByHelper = in_array(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? '',
            ['egdump', 'dgdump'],
            true
        );

        return gdumpOptions()
            ->setTraceIndex($calledByHelper ? 2 : 1)
            ->dump(...$vars);
    }
}

if (!function_exists('egdump')) {
    /**
     * Dump the passed variables and echo the result.
     *
     * @param mixed ...$vars
     *
     * @return void
     */
    function egdump(mixed ...$vars): void
    {
        echo gdump(...$vars);
    }
}

if (!function_exists('dgdump')) {
    /**
     * Dump the passed variables and die.
     *
     * @param mixed ...$vars
     *
     * @return never
     */
    function dgdump(mixed ...$vars): never
    {
        exit(gdump(...$vars));
    }
}
