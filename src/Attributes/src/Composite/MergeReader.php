<?php

declare(strict_types=1);

namespace Spiral\Attributes\Composite;

final class MergeReader extends Composite
{
    protected function each(callable $resolver): iterable
    {
        foreach ($this->readers as $reader) {
            yield from $resolver($reader);
        }
    }
}
