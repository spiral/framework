<?php

declare(strict_types=1);

namespace Spiral\Router\Target;

/**
 * Targets to all actions in specific controller. Variation of Action without action constrain.
 *
 * Example: new Controller(HomeController::class);
 */
final class Controller extends AbstractTarget
{
    public function __construct(
        private readonly string $controller,
        int $options = 0,
        string $defaultAction = 'index'
    ) {
        parent::__construct(
            ['action' => null],
            ['action' => null],
            $options,
            $defaultAction
        );
    }

    protected function resolveController(array $matches): string
    {
        return $this->controller;
    }

    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}
