<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Internal\Tracer\Trace;

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
        return $this->traces === [] ? '' : "Container trace list:\n" . $this->renderTraceList($this->traces);
    }

    /**
     * @param string $header Message before stack list
     */
    public function combineTraceMessage(string $header): string
    {
        return "$header\n$this";
    }

    public function push(bool $nextLevel, mixed ...$details): void
    {
        $trace = $details === [] ? null : new Trace($details);
        if ($nextLevel || $this->traces === []) {
            $this->traces[] = $trace === null ? [] : [$trace];
        } elseif ($trace !== null) {
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
    }

    public function getRootAlias(): string
    {
        return $this->traces[0][0]->alias ?? '';
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
        return \implode("\n", $result);
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
