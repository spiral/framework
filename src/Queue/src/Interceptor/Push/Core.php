<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\CoreInterface;
use Spiral\Queue\ExtendedOptionsInterface;
use Spiral\Queue\Options;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

/**
 * @psalm-type TParameters = array{options: ?OptionsInterface, payload: array}
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly QueueInterface $connection,
        private readonly ?TracerInterface $tracer = new NullTracer()
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
        \assert($parameters['options'] === null || $parameters['options'] instanceof OptionsInterface);
        \assert(\is_array($parameters['payload']));

        if ($parameters['options'] === null) {
            $parameters['options'] = new Options();
        }

        if (\method_exists($parameters['options'], 'withHeader')) {
            foreach ($this->tracer->getContext() as $key => $data) {
                $parameters['options'] = $parameters['options']->withHeader($key, $data);
            }
        }

        return $this->connection->push(
            name: $controller,
            payload: $parameters['payload'],
            options: $parameters['options']
        );
    }
}
