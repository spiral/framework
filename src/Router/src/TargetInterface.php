<?php

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Targets provide logical and constrained bridge between route and specific function or controller.
 *
 * @psalm-import-type Matches from UriHandler
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
     * @param Matches $matches
     *
     * @throws \Spiral\Router\Exception\TargetException
     */
    public function getHandler(ContainerInterface $container, array $matches): Handler;
}
