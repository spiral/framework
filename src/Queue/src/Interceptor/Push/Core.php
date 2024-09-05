<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\ContainerScope;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

/**
 * @internal
 * @psalm-type TParameters = array{options: ?OptionsInterface, payload: mixed}
 */
final class Core implements CoreInterface, HandlerInterface
{
    public function __construct(
        private readonly QueueInterface $connection,
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     * @deprecated
     */
    public function callAction(
        string $controller,
        string $action,
        array $parameters = ['options' => null, 'payload' => []],
    ): string {
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
                options: $parameters['options'],
            ),
            attributes: [
                'queue.handler' => $controller,
            ],
        );
    }

    public function handle(CallContextInterface $context): mixed
    {
        $args = $context->getArguments();
        $controller = $context->getTarget()->getPath()[0];
        $action = $context->getTarget()->getPath()[1];

        return $this->callAction($controller, $action, $args);
    }

    private function getTracer(): TracerInterface
    {
        try {
            return ContainerScope::getContainer()->get(TracerInterface::class);
        } catch (\Throwable) {
            return new NullTracer();
        }
    }
}
