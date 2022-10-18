<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use ReflectionFunction;
use Spiral\Core\Exception\Traits\ClosureRendererTrait;

/**
 * @internal
 */
final class Trace implements \Stringable
{
    use ClosureRendererTrait;

    private const ARRAY_MAX_LEVEL = 3;

    public readonly ?string $alias;

    public function __construct(
        public array $info = [],
    ) {
        $this->alias = $info['alias'] ?? null;
    }

    public function __toString(): string
    {
        $info = [];
        foreach ($this->info as $key => $item) {
            $info[] = "$key: {$this->stringifyValue($item)}";
        }
        return \implode("\n", $info);
    }

    private function stringifyValue(mixed $item): string
    {
        return match (true) {
            \is_string($item) => "'$item'",
            \is_scalar($item) => \var_export($item, true),
            $item instanceof \Closure => $this->renderClosureSignature(new \ReflectionFunction($item)),
            $item instanceof \ReflectionFunctionAbstract => $this->renderClosureSignature($item),
            $item instanceof \UnitEnum => $item::class . "::$item->name",
            \is_object($item) => 'instance of ' . $item::class,
            \is_array($item) => $this->renderArray($item),
            default => \gettype($item),
        };
    }

    private function renderArray(array $array, int $level = 0): string
    {
        if ($array === []) {
            return '[]';
        }
        if ($level >= self::ARRAY_MAX_LEVEL) {
            return 'array';
        }
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = \sprintf(
                '%s: %s',
                $key,
                \is_array($value)
                    ? $this->renderArray($value, $level + 1)
                    : $this->stringifyValue($value),
            );
        }

        $pad = \str_repeat('  ', $level);
        return "[\n  $pad" . \implode(",\n  $pad", $result) . "\n$pad]";
    }
}
