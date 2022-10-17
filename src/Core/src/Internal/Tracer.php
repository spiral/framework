<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
final class Tracer implements \Stringable
{
    /**
     * Trace blocks
     *
     * @var Trace[][]
     */
    private array $traces = [];

    public function __toString(): string
    {
        return $this->traces === [] ? '' : 'Container trace list:' . PHP_EOL . $this->renderTraceList($this->traces);
    }

    /**
     * @param string $header Message before stack list
     * @param bool $lastBlock Generate trace list only for last block
     * @param bool $clear Remove touched trace list
     */
    public function combineTraceMessage(string $header, bool $lastBlock = false, bool $clear = false): string
    {
        return "$header\n$this";
    }

    public function push(string $alias, bool $nextLevel = false, mixed ...$details): void
    {
        $trace = new Trace($alias, $details);
        if ($nextLevel || $this->traces === []) {
            $this->traces[] = [$trace];
        } else {
            $this->traces[\array_key_last($this->traces)][] = $trace;
        }
    }

    public function pop(bool $previousLevel = false): void
    {
        if ($this->traces === []) {
            return;
        }
        if ($previousLevel) {
            \array_pop($this->traces);
            return;
        }
        $key = \array_key_last($this->traces);
        $list = &$this->traces[$key];
        \array_pop($list);
        if ($list === []) {
            unset($this->traces[$key]);
        }
    }

    public function getRootAlias(): string
    {
        return $this->traces[0][0]->alias;
    }

    /**
     * @param Trace[][] $blocks
     */
    private function renderTraceList(array $blocks): string
    {
        $result = [];
        $i = 0;
        foreach ($blocks as $block) {
            \array_push($result, ...$this->blockToStringList($block, $i++));
        }
        return \implode(PHP_EOL, $result);
    }

    /**
     * @param Trace[] $items
     * @param int<0, max> $level
     *
     * @return string[]
     */
    private function blockToStringList(array $items, int $level = 0): array
    {
        $result = [];
        $padding = \str_repeat('  ', $level);
        $firstPrefix = "$padding- ";
        // Separator
        $s = "\n";
        $nexPrefix = "$s$padding  ";
        foreach ($items as $item) {
            $result[] = $firstPrefix . \str_replace($s, $nexPrefix, (string)$item);
        }
        return $result;
    }
}
