<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Targets provide logical and constrained bridge between route and specific function or controller.
 */
interface TargetInterface
{
    /**
     * Set of default values provided by the target.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Set of constrains defines list of required keys and optional set of allowed values.
     *
     * Examples:
     * ["controller" => null, "action" => "login"]
     * ["action" => ["login", "logout"]]
     *
     * @return array
     */
    public function getConstrains(): array;

    /**
     * Generates target handler.
     *
     * @param ContainerInterface $container
     * @param array              $matches
     * @return Handler
     *
     * @throws \Spiral\Router\Exception\TargetException
     */
    public function getHandler(ContainerInterface $container, array $matches): Handler;
}
