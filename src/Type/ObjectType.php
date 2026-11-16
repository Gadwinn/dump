<?php

declare(strict_types=1);

namespace G\Dump\Type;

use __PHP_Incomplete_Class;
use ReflectionObject;
use ReflectionProperty;

class ObjectType extends AbstractType
{
    private const string ANONYMOUS_CLASS = 'class@anonymous';
    private const string VISIBILITY_PUBLIC = 'public';
    private const string VISIBILITY_PROTECTED = 'protected';
    private const string VISIBILITY_PRIVATE = 'private';

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
        $output = [
            $theme->output(
                type: 'object',
                complement: $theme->escape(
                    // The real name of an anonymous class holds a null byte and a file path
                    ($reflection->isAnonymous() ? self::ANONYMOUS_CLASS : $reflection->getName())
                    . rtrim(' ' . trim($this->getInheritanceInfo($reflection)))
                )
            )
        ];

        if (
            !$var instanceof __PHP_Incomplete_Class &&
            $reflection->getProperties()
        ) {
            $dumper->increaseIndent();
            foreach ($reflection->getProperties() as $property) {
                if ($property->isInitialized($var)) {
                    $visibility = $this->getVisibility($property);
                    $output[] = sprintf(
                        '%s%s%s %s = %s',
                        $dumper->getIndent(),
                        $theme->output(type: 'visibility', value: $visibility),
                        $property->isStatic() ?
                            ' ' . $theme->output(type: 'visibility', value: 'static') : '',
                        $theme->output(type: 'key', value: '$' . $theme->escape($property->getName())),
                        $dumper->dump($property->getValue($var))
                    );
                }
            }
            $dumper->decreaseIndent();
        }

        return implode("\n", $output);
    }

    private function getVisibility(ReflectionProperty $property): string
    {
        return match (true) {
            $property->isPrivate() => self::VISIBILITY_PRIVATE,
            $property->isProtected() => self::VISIBILITY_PROTECTED,
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
