<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Stringable;
use Traversable;
use Spiral\Broadcasting\BroadcastInterface;

abstract class AbstractBroadcast implements BroadcastInterface
{
    /**
     * Format the topic array into an array of strings.
     *
     * @param string[]|Stringable[] $topics
     * @return string[]
     */
    protected function formatTopics(array $topics): array
    {
        return array_map(fn($topic) => (string)$topic, $topics);
    }

    /**
     * @template T of mixed
     * @param iterable<T>|T $entries
     * @return array<T>
     */
    protected function toArray($entries): array
    {
        switch (true) {
            case \is_array($entries):
                return $entries;

            case $entries instanceof Traversable:
                return \iterator_to_array($entries, false);

            default:
                return [$entries];
        }
    }
}
