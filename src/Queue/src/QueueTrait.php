<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Queue\Job\ObjectJob;

trait QueueTrait
{
    /**
     * @deprecated since v4.0. Use {@see QueueInterface::push()} instead.
     */
    public function pushObject(object $job, ?OptionsInterface $options = null): string
    {
        return $this->push(ObjectJob::class, ['object' => $job], $options);
    }
}
