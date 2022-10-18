<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Closure;
use ReflectionFunction;
use Spiral\Core\Exception\Traits\ClosureRendererTrait;

/**
 * @internal
 */
final class Trace implements \Stringable
{
    use ClosureRendererTrait;

    public function __construct(
        public readonly string $alias,
        public array $info,
    ) {
    }

    public function __toString(): string
    {
        $info = [$this->alias];
        foreach ($this->info as $key => $item) {
            $info[] = "$key: {$this->stringifyValue($item)}";
        }
        return \implode("\n", $info);
    }

    private function stringifyValue(mixed $item): string
    {
        return match (true) {
            \is_string($item) => "'$item'",
            \is_scalar($item) => (string)$item,
            $item instanceof Closure => $this->renderClosureSignature(new ReflectionFunction($item)),
            \is_object($item) => 'instance of ' . $item::class,
            default => \gettype($item),
        };
    }
}
