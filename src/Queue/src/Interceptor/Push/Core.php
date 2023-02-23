<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\ContainerScope;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

/**
 * @internal
 * @psalm-type TParameters = array{options: ?OptionsInterface, payload: array}
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly QueueInterface $connection
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     */
    public function callAction(
        string $controller,
        string $action,
        array $parameters = ['options' => null, 'payload' => []]
    ): string {
        \assert(\is_array($parameters['payload']));

        if ($parameters['options'] === null) {
            $parameters['options'] = new Options();
        }

        $tracer = $this->getTracer();

        if (\method_exists($parameters['options'], 'withHeader')) {
            foreach ($tracer->getContext() as $key => $data) {
                $parameters['options'] = $parameters['options']->withHeader($key, $data);
            }
        }

        return $tracer->trace(
            name: \sprintf('Job push [%s]', $controller),
            callback: fn (): string => $this->connection->push(
                name: $controller,
                payload: $parameters['payload'],
                options: $parameters['options']
            ),
            attributes: [
                'queue.handler' => $controller,
            ]
        );
    }

    private function getTracer(): TracerInterface
    {
        try {
            return ContainerScope::getContainer()->get(TracerInterface::class);
        } catch (\Throwable $e) {
            return new NullTracer();
        }
    }
}
