<?php

declare(strict_types=1);

namespace Spiral\Attributes\Composite;

final class SelectiveReader extends Composite
{
    protected function each(callable $resolver): iterable
    {
        foreach ($this->readers as $reader) {
            $result = $this->iterableToArray($resolver($reader));

            if ($result !== []) {
                return $result;
            }
        }

        return [];
    }
}
