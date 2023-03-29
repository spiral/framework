<?php

declare(strict_types=1);

namespace Spiral\Queue\Driver;

use Ramsey\Uuid\Uuid;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueTrait;

final class NullDriver implements QueueInterface
{
    use QueueTrait;

    public function push(string $name, mixed $payload = [], OptionsInterface $options = null): string
    {
        // Do nothing

        return (string) Uuid::uuid4();
    }
}
