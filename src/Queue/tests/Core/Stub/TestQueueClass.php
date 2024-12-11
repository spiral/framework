<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Core\Stub;

use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;

final class TestQueueClass implements QueueInterface
{
    public function push(string $name, mixed $payload = [], ?OptionsInterface $options = null): string
    {
        return $name;
    }
}
