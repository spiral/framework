<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface QueueInterface
{
    /**
     * @param class-string<HandlerInterface> $name
     * @param array $payload
     * @param OptionsInterface|null $options
     * @return string
     */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string;
}
