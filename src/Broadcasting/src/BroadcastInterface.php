<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Spiral\Broadcasting\Exception\BroadcastException;
use Stringable;

/**
 * @psalm-type TopicsList = non-empty-list<string> | non-empty-list<Stringable> | string | Stringable
 * @psalm-type MessagesList = non-empty-list<string> | non-empty-list<Stringable> | string | Stringable
 */
interface BroadcastInterface
{
    /**
     * Method to send messages to the required topic (channel).
     * <code>
     *  $broadcast->publish('topic', 'message');
     *  $broadcast->publish('topic', ['message 1', 'message 2']);
     *
     *  $broadcast->publish(['topic 1', 'topic 2'], 'message');
     *  $broadcast->publish(['topic 1', 'topic 2'], ['message 1', 'message 2']);
     * </code>
     *
     * Note: In future major releases, the signature of this method will be
     * changed to include follow type-hints.
     *
     * <code>
     *  public function publish(iterable|string|\Stringable $topics, iterable|string $messages): void;
     * </code>
     *
     * @param TopicsList $topics
     * @param MessagesList $messages
     * @throws BroadcastException
     */
    public function publish($topics, $messages): void;
}
