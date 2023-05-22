<?php

declare(strict_types=1);

namespace Spiral\Router\Target;

use Spiral\Router\UriHandler;

/**
 * Provides ability to invoke from a given controller set:
 *
 * Example: new Group(['signup' => SignUpController::class]);
 *
 * @psalm-import-type Matches from UriHandler
 */
final class Group extends AbstractTarget
{
    public function __construct(
        private readonly array $controllers,
        int $options = 0,
        string $defaultAction = 'index'
    ) {
        parent::__construct(
            ['controller' => null, 'action' => null],
            ['controller' => \array_keys($controllers), 'action' => null],
            $options,
            $defaultAction
        );
    }

    /**
     * @param Matches $matches
     */
    protected function resolveController(array $matches): string
    {
        return $this->controllers[$matches['controller']];
    }

    /**
     * @param Matches $matches
     */
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}
