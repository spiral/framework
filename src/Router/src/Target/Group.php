<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Target;

/**
 * Provides ability to invoke from a given controller set:
 *
 * Example: new Group(['signup' => SignUpController::class]);
 */
final class Group extends AbstractTarget
{
    /** @var array */
    private $controllers;

    public function __construct(array $controllers, int $options = 0, string $defaultAction = 'index')
    {
        $this->controllers = $controllers;
        parent::__construct(
            ['controller' => null, 'action' => null],
            ['controller' => array_keys($controllers), 'action' => null],
            $options,
            $defaultAction
        );
    }

    /**
     * @inheritdoc
     */
    protected function resolveController(array $matches): string
    {
        return $this->controllers[$matches['controller']];
    }

    /**
     * @inheritdoc
     */
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}
