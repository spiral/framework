<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
final class Trace implements \Stringable
{
    public function __construct(
        public readonly string $alias,
        public readonly string $information,
        public ?string $context = null
    ) {
        $this->context ??= '-';
    }

    public function __toString(): string
    {
        $result = [];
        $result[] = '- ' . $this->alias;
        $result[] = '    Info: ' . $this->information;
        $result[] = '    Context: ' . $this->context;

        return \implode(PHP_EOL, $result);
    }
}
