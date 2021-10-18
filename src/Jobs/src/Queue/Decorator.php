<?php

declare(strict_types=1);

namespace Spiral\Jobs\Queue;

use Spiral\Jobs\Options;
use Spiral\Jobs\QueueInterface;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\OptionsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface as RRQueueInterface;
use Spiral\RoadRunner\Jobs\Task\PreparedTaskInterface;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;

class Decorator implements QueueInterface
{
    /**
     * @var RRQueueInterface
     */
    private RRQueueInterface $queue;

    /**
     * @var JobsInterface
     */
    private JobsInterface $jobs;

    /**
     * @param JobsInterface $jobs
     * @param RRQueueInterface $queue
     */
    public function __construct(JobsInterface $jobs, RRQueueInterface $queue)
    {
        $this->queue = $queue;
        $this->jobs = $jobs;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->queue->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): OptionsInterface
    {
        return $this->queue->getDefaultOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function withDefaultOptions(?OptionsInterface $options): RRQueueInterface
    {
        return new self($this->jobs, $this->queue->withDefaultOptions($options));
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $name, array $payload = [], OptionsInterface $options = null): PreparedTaskInterface
    {
        return $this->getContext($options)
            ->create($name, $payload, $options)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(PreparedTaskInterface $task): QueuedTaskInterface
    {
        return $this->queue->dispatch($task);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchMany(PreparedTaskInterface ...$tasks): iterable
    {
        return $this->queue->dispatchMany(...$tasks);
    }

    /**
     * {@inheritDoc}
     */
    public function pause(): void
    {
        $this->queue->pause();
    }

    /**
     * {@inheritDoc}
     */
    public function resume(): void
    {
        $this->queue->resume();
    }

    /**
     * {@inheritDoc}
     */
    public function isPaused(): bool
    {
        return $this->queue->isPaused();
    }

    /**
     * {@inheritDoc}
     * @throws JobsException
     */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string
    {
        $context = $this->getContext($options);

        $task = $context->dispatch($context->create($name, $payload, $options));

        return $task->getId();
    }

    /**
     * @param OptionsInterface|null $options
     * @return QueueInterface
     */
    private function getContext(?OptionsInterface $options): QueueInterface
    {
        if ($options instanceof Options && $options->getPipeline() !== null) {
            $original = $this->jobs->connect($options->getPipeline());

            return new self($this->jobs, $original);
        }

        return $this;
    }
}
