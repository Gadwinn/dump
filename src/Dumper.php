<?php

declare(strict_types=1);

namespace G\Dump;

use G\Dump\Type\TypeInterface;
use G\Dump\Theme\{ThemeHTML, ThemeInterface};
use DirectoryIterator;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Dumper implements DumperInterface
{
    public const int DEFAULT_MAX_DEPTH = 10;

    /** Value of the trace index that hides the trace line. */
    public const int TRACE_DISABLED = -1;

    private const string TYPE_FILE_EXTENSION = 'php';
    private const string UNKNOWN_FILE = 'unknown';

    private int $indent = 0;
    private int $iteration = 0;

    /** @var array<int, true> Objects being dumped, by object id (recursion guard) */
    private array $references = [];

    /**
     * @var int $traceIndex Index of the trace line. If -1, trace line will be disabled.
     */
    protected int $traceIndex = 0;

    /** @var TypeInterface[] */
    private array $types;

    /**
     * @param ThemeInterface $theme Override default theme.
     * @param TypeInterface[] $types Override default types.
     *
     * If not provided, all types from the Type directory will be registered.
     * @param int $maxDepth Nesting level after which arrays and objects are not expanded any more.
     * @param int $maxStringLength Number of characters after which a string is cut (0 = no limit).
     * @param bool $showMethods List the methods of the objects, with their signatures.
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function __construct(
        private ThemeInterface $theme = new ThemeHTML(),
        array $types = [],
        private readonly int $maxDepth = self::DEFAULT_MAX_DEPTH,
        private readonly int $maxStringLength = 0,
        private readonly bool $showMethods = false,
    ) {
        $this->types = $types;

        if (empty($this->types)) {
            $this->registerTypes();
        }

        if (empty($this->types)) {
            throw new RuntimeException('No types registered.');
        }

        $this->types = $this->sortTypes(...$this->types);
    }

    private function registerTypes(): void
    {
        $typesDir = new DirectoryIterator(__DIR__ . '/Type');
        $this->types = [];

        foreach ($typesDir as $file) {
            if ($file->isFile() && $file->getExtension() === self::TYPE_FILE_EXTENSION) {
                $className = 'G\\Dump\\Type\\' . $file->getBasename('.php');

                if (!class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);

                if (
                    $reflection->implementsInterface(TypeInterface::class) &&
                    $reflection->isInstantiable()
                ) {
                    $type = $reflection->getConstructor() ?
                        $reflection->newInstance($this) :
                        $reflection->newInstance();

                    if ($type instanceof TypeInterface) {
                        $this->types[] = $type;
                    }
                }
            }
        }
    }

    /**
     * @param TypeInterface ...$types
     *
     * @return TypeInterface[]
     */
    private function sortTypes(TypeInterface ...$types): array
    {
        usort($types, static fn(TypeInterface $a, TypeInterface $b): int => $b->getPriority() <=> $a->getPriority());

        return $types;
    }

    public function getTraceIndex(): int
    {
        return $this->traceIndex;
    }

    public function setTraceIndex(int $traceIndex): static
    {
        $this->traceIndex = max(self::TRACE_DISABLED, $traceIndex);

        return $this;
    }

    public function getTheme(): ThemeInterface
    {
        return $this->theme;
    }

    public function getMaxStringLength(): int
    {
        return max(0, $this->maxStringLength);
    }

    public function showsMethods(): bool
    {
        return $this->showMethods;
    }

    public function getIndent(): string
    {
        return $this->theme->indent($this->indent);
    }

    public function increaseIndent(): void
    {
        $this->indent++;
    }

    public function decreaseIndent(): void
    {
        $this->indent--;
    }

    public function dump(mixed ...$vars): string
    {
        $output = [];

        if ($this->iteration === 0) {
            $output[] = $this->theme->header();
            if ($this->traceIndex !== self::TRACE_DISABLED) {
                $output[] = $this->getTraceLine();
            }
        }

        if (empty($vars)) {
            $output[] = $this->theme->output('', value: '*EMPTY DUMP*');
        } else {
            foreach (array_values($vars) as $i => $var) {
                $id = is_object($var) ? spl_object_id($var) : null;
                if ($id !== null && isset($this->references[$id])) {
                    $output[] = $this->theme->output('', value: '*RECURSION*');
                    continue;
                }

                if ($this->iteration > $this->maxDepth && (is_array($var) || is_object($var))) {
                    $output[] = $this->theme->output('', value: '*MAX DEPTH*');
                    continue;
                }

                foreach ($this->types as $type) {
                    if ($type->supports($var)) {
                        if ($this->iteration === 0 && $i) {
                            $output[] = '';
                        }
                        if ($id !== null) {
                            $this->references[$id] = true;
                        }
                        $this->iteration++;
                        try {
                            $output[] = $type->dump($var);
                        } finally {
                            $this->iteration--;
                            if ($id !== null) {
                                unset($this->references[$id]);
                            }
                        }
                        continue 2;
                    }
                }

                // Type not found
                $output[] = $this->theme->output('', value: '*TYPE (' . gettype($var) . ') NOT FOUND*');
            }
        }

        if ($this->iteration === 0) {
            $output[] = $this->theme->footer();
        }

        return implode(PHP_EOL, $output);
    }

    private function getTraceLine(): string
    {
        // Frame 0 is the call to this method, frame 1 the call to dump()
        $index = $this->traceIndex + 1;
        $frame = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $index + 1)[$index] ?? [];

        return $this->theme->output(
            type: 'trace',
            value: $this->theme->escape(($frame['file'] ?? self::UNKNOWN_FILE) . ':' . ($frame['line'] ?? 0))
        );
    }
}
