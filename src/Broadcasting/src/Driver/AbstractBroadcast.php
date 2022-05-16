<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Spiral\Broadcasting\BroadcastInterface;

abstract class AbstractBroadcast implements BroadcastInterface
{
    /**
     * Format the topic array into an array of strings.
     *
     * @param string[]|\Stringable[] $topics
     * @return string[]
     */
    protected function formatTopics(array $topics): array
    {
        return \array_map(fn (string|\Stringable $topic) => (string) $topic, $topics);
    }

    /**
     * @template T of mixed
     * @param iterable<T>|T $entries
     * @return array<T>
     */
    protected function toArray(iterable|string|\Stringable $entries): array
    {
        return match (true) {
            \is_array($entries) => $entries,
            $entries instanceof \Traversable => \iterator_to_array($entries, false),
            default => [$entries],
        };
    }
}
