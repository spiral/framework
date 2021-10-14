<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\RoadRunner\Worker;

/***
 * @codeCoverageIgnore handled on Golang end.
 */
final class Consumer
{
    /** @var HandlerRegistryInterface */
    private $registry;

    /**
     * @codeCoverageIgnore
     * @param HandlerRegistryInterface $registry
     */
    public function __construct(HandlerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @codeCoverageIgnore
     * @param Worker        $worker
     * @param callable|null $finalize
     */
    public function serve(Worker $worker, callable $finalize = null): void
    {
        while ($body = $worker->receive($context)) {
            try {
                $context = json_decode($context, true);
                $handler = $this->registry->getHandler($context['job']);

                $handler->handle($context['job'], $context['id'], $body);

                $worker->send('ok');
            } catch (\Throwable $e) {
                $worker->error((string)$e->getMessage());
            } finally {
                if ($finalize !== null) {
                    call_user_func($finalize, $e ?? null);
                }
            }
        }
    }
}
