<?php

declare(strict_types=1);

namespace G\Dump\Type;

use G\Dump\DumperInterface;

abstract class AbstractType implements TypeInterface
{
    public function __construct(
        protected DumperInterface $dumper
    ) { //
    }
}
