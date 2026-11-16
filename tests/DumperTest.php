<?php

declare(strict_types=1);

namespace G\Dump\Tests;

use G\Dump\Dumper;
use G\Dump\Theme\ThemeHTML;
use G\Dump\Theme\ThemeText;
use G\Dump\Type\IntegerType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DumperTest extends TestCase
{
    private function text(mixed ...$vars): string
    {
        return (new Dumper(new ThemeText()))->setTraceIndex(Dumper::TRACE_DISABLED)->dump(...$vars);
    }

    private function html(mixed ...$vars): string
    {
        return (new Dumper(new ThemeHTML()))->setTraceIndex(Dumper::TRACE_DISABLED)->dump(...$vars);
    }

    #[DataProvider('scalarProvider')]
    public function testScalars(mixed $value, string $expected): void
    {
        $this->assertSame($expected, trim($this->text($value)));
    }

    /** @return array<string, array{mixed, string}> */
    public static function scalarProvider(): array
    {
        return [
            'null' => [null, 'NULL'],
            'true' => [true, 'boolean true'],
            'false' => [false, 'boolean false'],
            'int' => [-42, 'int -42'],
            'float' => [1.5, 'float 1.5'],
            'string' => ['abc', "string(3) 'abc'"],
            'empty string' => ['', "string(0) ''"],
            'multibyte string' => ['été', "string(3 - size=5) 'été'"],
            'numeric string' => ['1.23', "string(4) '1.23'"],
        ];
    }

    public function testNothingToDump(): void
    {
        $this->assertSame('*EMPTY DUMP*', trim($this->text()));
    }

    public function testSeveralValuesAreSeparatedByABlankLine(): void
    {
        $this->assertSame("int 1\n\nint 2", str_replace("\r\n", "\n", trim($this->text(1, 2))));
    }

    public function testNestedArray(): void
    {
        $expected = <<<'TXT'
            array(2)
            |- 0 => int 1
            |- 'list' => array(2)
            |  |- 'a' => NULL
            |  |- 0 => boolean true
            TXT;

        $this->assertSame($expected, str_replace("\r\n", "\n", trim($this->text([1, 'list' => ['a' => null, true]]))));
    }

    public function testObjectPropertiesWithVisibility(): void
    {
        $object = new class {
            public int $id = 7;
            protected string $name = 'x';
            private ?array $tags = null;
            public static bool $shared = true;
            public string $never;
        };

        $dump = $this->text($object);

        $this->assertStringStartsWith('object(class@anonymous)', trim($dump));
        $this->assertStringContainsString('|- public $id = int 7', $dump);
        $this->assertStringContainsString("|- protected \$name = string(1) 'x'", $dump);
        $this->assertStringContainsString('|- private $tags = NULL', $dump);
        $this->assertStringContainsString('|- public static $shared = boolean true', $dump);
        $this->assertStringNotContainsString('$never', $dump, 'Uninitialized properties are skipped');
    }

    public function testObjectHeaderShowsParentAndInterfaces(): void
    {
        $dump = $this->text(new \ArrayObject());

        $this->assertStringStartsWith('object(ArrayObject implements ', trim($dump));
        $this->assertStringContainsString('Countable', $dump);
    }

    public function testRecursiveObjectIsDetected(): void
    {
        $object = new \stdClass();
        $object->self = $object;
        $object->other = new \stdClass();

        $dump = $this->text($object);

        $this->assertStringContainsString('|- public $self = *RECURSION*', $dump);
        $this->assertStringContainsString('|- public $other = object(stdClass)', $dump);
    }

    public function testTheSameObjectTwiceIsNotARecursion(): void
    {
        $shared = new \stdClass();

        $this->assertStringNotContainsString('*RECURSION*', $this->text([$shared, $shared]));
    }

    public function testEqualScalarsAreNotARecursion(): void
    {
        $this->assertStringNotContainsString('*RECURSION*', $this->text(['a', ['a', ['a']]]));
    }

    public function testMaxDepthStopsTheExpansion(): void
    {
        $dumper = (new Dumper(new ThemeText(), maxDepth: 1))->setTraceIndex(Dumper::TRACE_DISABLED);

        $dump = $dumper->dump(['level1' => ['level2' => ['level3' => 'deep']]]);

        $this->assertStringContainsString("'level2' => *MAX DEPTH*", $dump);
        $this->assertStringNotContainsString('deep', $dump);
    }

    public function testClosuresAndResourcesCanBeDumped(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);
        $closed = fopen('php://memory', 'r');
        $this->assertIsResource($closed);
        fclose($closed);

        $dump = $this->text([static fn (): int => 1, $resource, $closed]);

        $this->assertStringContainsString('object(Closure)', $dump);
        $this->assertStringContainsString("'stream'", $dump);
        $this->assertStringContainsString('resource (closed)', $dump);
    }

    public function testJsonStringIsDecoded(): void
    {
        $dump = $this->text('{"name":"John","roles":["admin"]}');

        $this->assertStringStartsWith('json array(2)', trim($dump));
        $this->assertStringContainsString("'name' => string(4) 'John'", $dump);
        $this->assertStringContainsString("0 => string(5) 'admin'", $dump);
    }

    public function testSlashedJsonStringIsDecoded(): void
    {
        $this->assertStringStartsWith('json slashed array(1)', trim($this->text('{\"k\":\"v\"}')));
    }

    public function testSerializedStringIsDecoded(): void
    {
        $dump = $this->text(serialize(['a' => 1]));

        $this->assertStringStartsWith('serialized array(1)', trim($dump));
        $this->assertStringContainsString("'a' => int 1", $dump);
    }

    public function testDumpingASerializedObjectNeverInstantiatesIt(): void
    {
        WakeUpSpy::$woken = false;

        $dump = $this->text(serialize(new WakeUpSpy()));

        $this->assertFalse(WakeUpSpy::$woken, 'Dumping a string must not run __wakeup()');
        $this->assertStringContainsString('__PHP_Incomplete_Class', $dump);
    }

    public function testThrowableShowsMessageCodeAndPrevious(): void
    {
        $exception = new \RuntimeException('Outer', 12, new \LogicException('Inner'));

        $dump = $this->text($exception);

        $this->assertStringContainsString('exception(RuntimeException)', $dump);
        $this->assertStringContainsString("'Outer'", $dump);
        $this->assertStringContainsString('#12', $dump);
        $this->assertStringContainsString('previous = exception(LogicException)', $dump);
    }

    public function testTraceLinePointsToTheCaller(): void
    {
        $dump = (new Dumper(new ThemeText()))->dump(1);
        $line = __LINE__ - 1;

        $this->assertStringContainsString(__FILE__ . ':' . $line, $dump);
    }

    public function testHtmlThemeEscapesValuesKeysAndNames(): void
    {
        $object = new \stdClass();
        $object->{'<i>prop</i>'} = '<script>alert(1)</script>';

        $dump = $this->html(['<b>key</b>' => $object, 'e' => new \RuntimeException('<img src=x>')]);

        $this->assertStringNotContainsString('<script>', $dump);
        $this->assertStringNotContainsString('<b>key</b>', $dump);
        $this->assertStringNotContainsString('<i>prop</i>', $dump);
        $this->assertStringNotContainsString('<img', $dump);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $dump);
        $this->assertStringContainsString('&lt;b&gt;key&lt;/b&gt;', $dump);
    }

    public function testHtmlThemeWrapsTheDumpInAPreBlock(): void
    {
        $dump = trim($this->html('x'));

        $this->assertStringStartsWith('<pre', $dump);
        $this->assertStringEndsWith('</pre>', $dump);
    }

    public function testCustomTypeListReplacesTheDefaultOnes(): void
    {
        $dumper = new Dumper(new ThemeText());
        $onlyIntegers = (new Dumper(new ThemeText(), [new IntegerType($dumper)]))->setTraceIndex(Dumper::TRACE_DISABLED);

        $this->assertSame('int 5', trim($onlyIntegers->dump(5)));
        $this->assertStringContainsString('*TYPE (string) NOT FOUND*', $onlyIntegers->dump('x'));
    }

    public function testDumperCanBeReusedAfterAFailingType(): void
    {
        $dumper = (new Dumper(new ThemeText()))->setTraceIndex(Dumper::TRACE_DISABLED);

        $first = $dumper->dump(['a' => ['b' => 1]]);
        $second = $dumper->dump(['a' => ['b' => 1]]);

        $this->assertSame($first, $second);
    }
}
