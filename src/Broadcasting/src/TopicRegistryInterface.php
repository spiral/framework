<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

interface TopicRegistryInterface
{
    public function register(string $topic, callable $callback): void;

    public function findCallback(string $topic, array &$matches): ?callable;
}
