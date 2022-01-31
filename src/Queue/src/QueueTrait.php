<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Queue\Job\CallableJob;
use Spiral\Queue\Job\ObjectJob;

trait QueueTrait
{
    /**
     * @param object $job
     * @param OptionsInterface|null $options
     * @return string
     */
    public function pushObject(object $job, OptionsInterface $options = null): string
    {
        return $this->push(ObjectJob::class, ['object' => $job], $options);
    }

    public function pushCallable(\Closure $job, OptionsInterface $options = null): string
    {
        return $this->push(CallableJob::class, ['callback' => $job], $options);
    }
}
