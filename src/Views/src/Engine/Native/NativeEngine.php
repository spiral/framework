<?php

declare(strict_types=1);

namespace Spiral\Views\Engine\Native;

use Psr\Container\ContainerInterface;
use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\AbstractEngine;
use Spiral\Views\ViewInterface;

final class NativeEngine extends AbstractEngine
{
    protected const EXTENSION = 'php';

    public function __construct(
        private readonly ContainerInterface $container,
        string $extension = self::EXTENSION
    ) {
        $this->extension = $extension;
    }

    public function compile(string $path, ContextInterface $context): mixed
    {
        // doing nothing, native views can not be compiled
        return null;
    }

    public function reset(string $path, ContextInterface $context): void
    {
        // doing nothing, native views can not be compiled
    }

    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return new NativeView($this->getLoader()->load($path), $this->container);
    }
}
