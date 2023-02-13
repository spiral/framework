<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface QueueInterface
{
    /**
     * @param string|class-string<HandlerInterface> $name
     * @param OptionsInterface|null $options
     */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string;
}
