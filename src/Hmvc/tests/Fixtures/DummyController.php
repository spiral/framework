<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Psr\Container\ContainerInterface;
use Spiral\Core\ContainerScope;

class DummyController
{
    public static function inner(): void {}

    public function index(string $name = 'Dave')
    {
        return "Hello, {$name}.";
    }

    public function required(int $id)
    {
        return $id;
    }

    public function scope(int $id, ContainerInterface $controller)
    {
        return $controller;
    }

    public function globalScope(int $id)
    {
        return ContainerScope::getContainer();
    }
}
