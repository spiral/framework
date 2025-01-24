<?php

namespace Spiral\Tests\Cache\Core;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheManager;
use Spiral\Cache\CacheRepository;
use Spiral\Cache\Config\CacheConfig;
use Spiral\Cache\Core\CacheInjector;
use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\FactoryInterface;

final class CacheInjectorTest extends TestCase
{
    private ?CacheInterface $defaultCache = null;

    public function testGetByContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new \ReflectionClass(ArrayStorage::class);

        $result = $injector->createInjection($reflection, 'array');

        self::assertInstanceOf(CacheRepository::class, $result);
        self::assertInstanceOf(ArrayStorage::class, $result->getStorage());
    }

    public function testGetByIncorrectContext(): void
    {
        $injector = $this->createInjector();
        $reflection = new \ReflectionClass(CacheInterface::class);

        $result = $injector->createInjection($reflection, 'userCache');

        // The default connection should be returned
        self::assertSame($this->defaultCache, $result->getStorage());
    }

    public function testBadArgumentTypeException(): void
    {
        $injector = $this->createInjector();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The cache obtained by the context');

        $reflection = new \ReflectionClass(ArrayStorage::class);
        $injector->createInjection($reflection, 'cache');
    }

    private function createInjector(): CacheInjector
    {
        $this->defaultCache = m::mock(CacheInterface::class);
        $config = new CacheConfig([
            'default' => 'test',
            'aliases' => [
                'config-cache' => 'roadrunner',
                'routes-cache' => 'array',
                'cache' => 'test',
                'test' => 'test',
            ],
            'typeAliases' => [],
            'storages' => [
                'array' => [
                    'type' => 'array',
                ],
                'test' => [
                    'type' => 'test',
                ],
            ],
        ]);
        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->andReturnUsing(function (string $name): CacheInterface {
            $result = [
                'test' => $this->defaultCache,
                'array' => new ArrayStorage(),
            ][$name] ?? null;
            if ($result === null) {
                throw new NotFoundException();
            }
            return $result;
        });
        $manager = new CacheManager($config, $factory);

        return new CacheInjector($manager);
    }
}
