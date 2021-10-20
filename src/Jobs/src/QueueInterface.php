<?php

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\RoadRunner\Jobs\OptionsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface as RoadRunnerQueueInterface;

interface QueueInterface extends RoadRunnerQueueInterface
{
    /**
     * @param class-string<HandlerInterface> $name
     * @param array $payload
     * @param OptionsInterface|null $options
     * @return string
     */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string;
}
