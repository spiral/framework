<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Spiral\Router\Exception\TargetException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Targets provide logical and constrained bridge between route and specific function or controller.
 */
interface TargetInterface
{
    /**
     * Set of default values provided by the target.
     */
    public function getDefaults(): array;

    /**
     * Set of constrains defines list of required keys and optional set of allowed values.
     *
     * Examples:
     * ["controller" => null, "action" => "login"]
     * ["action" => ["login", "logout"]]
     */
    public function getConstrains(): array;

    /**
     * Generates target handler.
     *
     *
     * @throws TargetException
     */
    public function getHandler(ContainerInterface $container, array $matches): Handler;
}
