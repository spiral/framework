<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\Goridge\RPC;

/**
 * Provides the ability to automatically specify the job pipeline.
 *
 * @deprecated since 2.9. Will be removed since 3.0
 */
final class JobQueue implements QueueInterface
{
    /** @var Queue */
    private $queue;

    /** @var JobRegistry */
    private $registry;

    /**
     * @param RPC|RPC\RPCInterface $rpc
     * @param JobRegistry $registry
     */
    public function __construct($rpc, JobRegistry $registry)
    {
        $this->queue = new Queue($rpc, $registry);
        $this->registry = $registry;
    }

    /**
     * @param string $jobType
     * @param array $payload
     * @param Options|null $options
     * @return string
     */
    public function push(string $jobType, array $payload = [], Options $options = null): string
    {
        if ($options === null) {
            $options = new Options();
        }

        $pipeline = $this->registry->getPipeline($jobType);
        if ($pipeline !== null && $options->getPipeline() === null) {
            $options = $options->withPipeline($pipeline);
        }

        return $this->queue->push($jobType, $payload, $options);
    }
}
