<?php

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\RoadRunner\Jobs\Options as RoadRunnerOptions;
use Spiral\RoadRunner\Jobs\OptionsInterface;

/**
 * @deprecated Since 2.9. Please use {@see RoadRunnerOptions} instead.
 */
class Options implements OptionsInterface, \JsonSerializable
{
    /**
     * @var OptionsInterface
     */
    private OptionsInterface $base;

    /**
     * @var string|null
     */
    private ?string $pipeline = null;

    /**
     * Options constructor
     */
    public function __construct()
    {
        $this->base = new RoadRunnerOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function getDelay(): int
    {
        return $this->base->getDelay();
    }

    /**
     * @param int|null $delay
     * @return $this
     */
    public function withDelay(?int $delay): self
    {
        $options = clone $this;
        $options->base = $options->base->withDelay($delay ?? 0);

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority(): int
    {
        return $this->base->getPriority();
    }

    /**
     * @return string|null
     */
    public function getPipeline(): ?string
    {
        return $this->pipeline;
    }

    /**
     * @param string|null $pipeline
     * @return self
     */
    public function withPipeline(?string $pipeline): self
    {
        $options = clone $this;
        $options->pipeline = $pipeline;

        return $options;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'delay'    => $this->base->delay ?: null,
            'pipeline' => $this->pipeline,
        ];
    }

    /**
     * @param positive-int|0 $delay
     * @return Options
     */
    public static function delayed(int $delay): self
    {
        $options = new self();
        $options->base = $options->base->withDelay($delay);

        return $options;
    }
}
