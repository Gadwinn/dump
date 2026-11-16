<?php

declare(strict_types=1);

namespace G\Dump\Type;

class SerializedType extends AbstractType
{
    private const int PRIORITY = 15;

    /** Cheap pre-check: only strings looking like serialize() output are unserialized. */
    private const string SERIALIZED_PATTERN = '/^(?:[aOCE]:\d+:|[sid]:|b:[01];)/';

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function supports(mixed $var): bool
    {
        // Objects are never instantiated: dumping a string must not run any __wakeup() or __destruct()
        return is_string($var)
            && preg_match(self::SERIALIZED_PATTERN, $var) === 1
            && @unserialize($var, ['allowed_classes' => false]) !== false;
    }

    /**
     * @param string $var
     */
    public function dump(mixed $var): string
    {
        $dumper = $this->dumper;

        return $dumper->getTheme()->output(
            type: 'serialized',
            value: $dumper->dump(unserialize($var, ['allowed_classes' => false]))
        );
    }
}
