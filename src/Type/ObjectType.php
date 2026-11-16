<?php

declare(strict_types=1);

namespace G\Dump\Type;

use __PHP_Incomplete_Class;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

class ObjectType extends AbstractType
{
    private const string ANONYMOUS_CLASS = 'class@anonymous';
    private const string VISIBILITY_PUBLIC = 'public';
    private const string VISIBILITY_PROTECTED = 'protected';
    private const string VISIBILITY_PRIVATE = 'private';
    private const string METHODS_TITLE = 'METHODS';
    private const string UNKNOWN_DEFAULT = '...';

    public function getPriority(): int
    {
        return 0;
    }

    public function supports(mixed $var): bool
    {
        return is_object($var);
    }

    /**
     * @param object $var
     */
    public function dump(mixed $var): string
    {
        $dumper = $this->dumper;
        $theme = $dumper->getTheme();
        $reflection = new ReflectionObject($var);
        $head = $theme->output(
            type: 'object',
            complement: $theme->escape(
                $this->className($reflection) . rtrim(' ' . trim($this->getInheritanceInfo($reflection)))
            )
        );

        if ($var instanceof __PHP_Incomplete_Class) {
            return $head;
        }

        $children = [];
        $dumper->increaseIndent();

        foreach ($reflection->getReflectionConstants() as $constant) {
            $children[] = $this->constantLine($constant);
        }

        foreach ($reflection->getProperties() as $property) {
            if ($property->isInitialized($var)) {
                $children[] = sprintf(
                    '%s%s%s %s = %s',
                    $dumper->getIndent(),
                    $theme->output(type: 'visibility', value: $this->getVisibility($property)),
                    $property->isStatic() ?
                        ' ' . $theme->output(type: 'visibility', value: 'static') : '',
                    $theme->output(type: 'key', value: '$' . $theme->escape($property->getName())),
                    $dumper->dump($property->getValue($var))
                );
            }
        }

        if ($dumper->showsMethods() && $reflection->getMethods() !== []) {
            $children[] = $this->methodsGroup($reflection);
        }

        $dumper->decreaseIndent();

        return $theme->nest($head, $children);
    }

    private function constantLine(ReflectionClassConstant $constant): string
    {
        $theme = $this->dumper->getTheme();
        $value = $constant->getValue();

        return sprintf(
            '%s%s %s %s = %s',
            $this->dumper->getIndent(),
            $theme->output(type: 'visibility', value: $this->getVisibility($constant)),
            $theme->output(type: 'visibility', value: $constant->isEnumCase() ? 'case' : 'const'),
            $theme->output(type: 'key', value: $theme->escape($constant->getName())),
            // An object constant (enum case...) is named, not expanded: its class lists the same constants again
            is_object($value)
                ? $theme->output(type: 'object', complement: $theme->escape($value::class))
                : $this->dumper->dump($value)
        );
    }

    /**
     * The signatures of the methods, as one group that themes may fold.
     */
    private function methodsGroup(ReflectionObject $reflection): string
    {
        $dumper = $this->dumper;
        $theme = $dumper->getTheme();
        $head = $dumper->getIndent() . $theme->output(type: 'key', value: self::METHODS_TITLE);

        $lines = [];
        $dumper->increaseIndent();
        foreach ($reflection->getMethods() as $method) {
            $lines[] = $dumper->getIndent() . $this->methodSignature($method);
        }
        $dumper->decreaseIndent();

        return $theme->nest($head, $lines, true);
    }

    private function methodSignature(ReflectionMethod $method): string
    {
        $theme = $this->dumper->getTheme();

        $modifiers = array_filter([
            $method->isAbstract() ? 'abstract' : ($method->isFinal() ? 'final' : ''),
            $this->getVisibility($method),
            $method->isStatic() ? 'static' : '',
            'function',
        ]);

        $parameters = array_map($this->parameterSignature(...), $method->getParameters());
        $returnType = $method->getReturnType();

        return sprintf(
            '%s %s(%s)%s',
            $theme->output(type: 'visibility', value: implode(' ', $modifiers)),
            $theme->output(type: 'key', value: $theme->escape($method->getName())),
            $theme->escape(implode(', ', $parameters)),
            $returnType !== null ? ': ' . $theme->escape((string) $returnType) : ''
        );
    }

    private function parameterSignature(ReflectionParameter $parameter): string
    {
        $signature = ($parameter->getType() !== null ? $parameter->getType() . ' ' : '')
            . ($parameter->isPassedByReference() ? '&' : '')
            . ($parameter->isVariadic() ? '...' : '')
            . '$' . $parameter->getName();

        if ($parameter->isDefaultValueAvailable()) {
            $signature .= ' = ' . $this->defaultValue($parameter);
        }

        return $signature;
    }

    private function defaultValue(ReflectionParameter $parameter): string
    {
        try {
            $default = $parameter->getDefaultValue();
        } catch (Throwable) {
            return self::UNKNOWN_DEFAULT;
        }

        return match (true) {
            $default === null => 'null',
            is_bool($default) => $default ? 'true' : 'false',
            is_int($default), is_float($default) => (string) $default,
            is_string($default) => "'" . $default . "'",
            $default === [] => '[]',
            default => self::UNKNOWN_DEFAULT,
        };
    }

    /**
     * The real name of an anonymous class holds a null byte and a file path.
     */
    private function className(ReflectionObject $reflection): string
    {
        return $reflection->isAnonymous() ? self::ANONYMOUS_CLASS : $reflection->getName();
    }

    private function getVisibility(ReflectionProperty|ReflectionClassConstant|ReflectionMethod $member): string
    {
        return match (true) {
            $member->isPrivate() => self::VISIBILITY_PRIVATE,
            $member->isProtected() => self::VISIBILITY_PROTECTED,
            default => self::VISIBILITY_PUBLIC,
        };
    }

    private function getInheritanceInfo(ReflectionObject $reflection): string
    {
        $info = [];

        if ($parent = $reflection->getParentClass()) {
            $info[] = sprintf(' extends %s', $parent->getName());
        }

        if ($interfaces = $reflection->getInterfaceNames()) {
            $info[] = sprintf(' implements %s', implode(', ', $interfaces));
        }

        return implode('', $info);
    }
}
