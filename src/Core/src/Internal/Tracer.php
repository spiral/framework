<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
final class Tracer implements \Stringable
{
    /**
     * @var Trace[]
     */
    private array $traces = [];

    public function __toString(): string
    {
        $result = [];
        if ($this->traces !== []) {
            $result = ['Container trace list:', ...$this->toStringList($this->traces)];
        }

        return \implode(PHP_EOL, $result);
    }

    private function toStringList(array $items, int $level = 0): array
    {
        $result = [];
        foreach ($items as $item) {
            if (\is_array($item)) {
                \array_push($result, ...$this->toStringList($item, $level + 1));
            } else {
                $padding ??= \str_repeat('  ', $level);
                $result[] = "$padding- " . \str_replace("\n", "\n$padding  ", (string)$item);
            }
        }
        return $result;
    }

    public function push(string $alias, array $details, bool $nextLevel = false): void
    {
        $list = &$this->traces;
        // Find trace bag
        if ($list === []) {
            $init = true;
        } else {
            $init = false;
            // $list = $this->traces[\array_key_last($this->traces)];
            while (\is_array($list) && $list !== []) {
                $key = \array_key_last($list);
                if (!\is_array($list[$key])) {
                    break;
                }
                $list = &$list[$key];
            }
        }
        $trace = new Trace($alias, $details);
        $list[] = $nextLevel && !$init
            ? [$trace]
            : $trace;
    }

    public function pop(): void
    {
        if ($this->traces === []) {
            return;
        }
        $list = $this->traces[\array_key_last($this->traces)];
        while (\is_array($list)) {
            $key = \array_key_last($list);
            if (!\is_array($list[$key])) {
                continue;
            }
            $list = &$list[$key];
        }
        $list = [];
    }

    public function getRootConstructedClass(): string
    {
        return $this->traces[0]->alias;
    }

    public function clean(): void
    {
        $this->traces = [];
    }
}
