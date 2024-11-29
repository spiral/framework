<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Router\Fixtures\TestRouterBootloader;

abstract class BaseTestingCase extends \Spiral\Testing\TestCase
{
    protected Router $router;

    public function defineBootloaders(): array
    {
        return [
            TestRouterBootloader::class,
            NyholmBootloader::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->getContainer()->get(RouterInterface::class);
    }

    /**
     * @throws \ReflectionException
     */
    protected function getProperty(object $object, string $property): mixed
    {
        $r = new \ReflectionObject($object);

        return $r->getProperty($property)->getValue($object);
    }
}
