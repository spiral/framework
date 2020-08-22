<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Engine\Native;

use Psr\Container\ContainerInterface;
use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\AbstractEngine;
use Spiral\Views\ViewInterface;

final class NativeEngine extends AbstractEngine
{
    protected const EXTENSION = 'php';

    /** @var ContainerInterface */
    private $container;

    /**
     * NativeEngine constructor.
     *
     * @param ContainerInterface $container
     * @param string             $extension
     */
    public function __construct(ContainerInterface $container, string $extension = self::EXTENSION)
    {
        $this->container = $container;
        $this->extension = $extension;
    }

    /**
     * @inheritdoc
     */
    public function compile(string $path, ContextInterface $context): void
    {
        // doing nothing, native views can not be compiled
    }

    /**
     * @inheritdoc
     */
    public function reset(string $path, ContextInterface $context): void
    {
        // doing nothing, native views can not be compiled
    }

    /**
     * @inheritdoc
     */
    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return new NativeView($this->getLoader()->load($path), $this->container, $context);
    }
}
