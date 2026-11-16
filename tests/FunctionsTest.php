<?php

declare(strict_types=1);

namespace G\Dump\Tests;

use G\Dump\Dumper;
use G\Dump\Theme\ThemeText;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    public function testGdumpReturnsTheDumpWithTheCallerLine(): void
    {
        $dump = gdump(['a' => 1]);
        $line = __LINE__ - 1;

        $this->assertStringContainsString(__FILE__ . ':' . $line, $dump);
        $this->assertStringContainsString("|- 'a' => int 1", $dump);
    }

    public function testEgdumpEchoesTheDumpWithTheCallerLine(): void
    {
        ob_start();
        egdump('hello');
        $line = __LINE__ - 1;
        $output = (string) ob_get_clean();

        $this->assertStringContainsString(__FILE__ . ':' . $line, $output);
        $this->assertStringContainsString("string(5) 'hello'", $output);
    }

    public function testGdumpOptionsKeepsTheSameDumperUntilReconfigured(): void
    {
        $dumper = gdumpOptions();

        $this->assertInstanceOf(Dumper::class, $dumper);
        $this->assertSame($dumper, gdumpOptions());
        $this->assertInstanceOf(ThemeText::class, $dumper->getTheme(), 'The text theme is used on the command line');
    }

    public function testGdumpOptionsAppliesANewThemeAndDepth(): void
    {
        $theme = new ThemeText();

        try {
            $dumper = gdumpOptions(themeCli: $theme, maxDepth: 1);

            $this->assertSame($theme, $dumper->getTheme());
            $this->assertStringContainsString('*MAX DEPTH*', gdump([[['deep']]]));
        } finally {
            gdumpOptions(maxDepth: Dumper::DEFAULT_MAX_DEPTH);
        }
    }
}
