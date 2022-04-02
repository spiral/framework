<?php

declare(strict_types=1);

namespace Spiral\Router\Target;

use Spiral\Router\Autofill;

/**
 * Targets to specific controller action or actions.
 *
 * Examples:
 *
 * new Action(HomeController::class, "index");
 * new Action(SingUpController::class, ["login", "logout"]); // creates <action> constrain
 */
final class Action extends AbstractTarget
{
    /**
     * @param string       $controller Controller class name.
     * @param string|array $action     One or multiple allowed actions.
     * @param int          $options    Action behaviour options.
     */
    public function __construct(
        private readonly string $controller,
        private readonly string|array $action,
        int $options = 0
    ) {
        if (\is_string($action)) {
            parent::__construct(
                ['action' => $action],
                ['action' => new Autofill($action)],
                $options
            );
        } else {
            parent::__construct(
                ['action' => null],
                ['action' => $action],
                $options
            );
        }
    }

    protected function resolveController(array $matches): string
    {
        return $this->controller;
    }

    protected function resolveAction(array $matches): string
    {
        $action = $this->action;
        if (\is_string($action)) {
            return $action;
        }

        return $matches['action'];
    }
}
