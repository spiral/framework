<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Http\Config\HttpConfig;
use Spiral\Tests\Framework\BaseTest;

final class HttpBootloaderTest extends BaseTest
{
    public function testDefaultInputBags(): void
    {
        $this->assertSame([], $this->getContainer()->get(HttpConfig::class)->getInputBags());
    }

    public function testAddInputBag(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(HttpConfig::CONFIG, ['inputBags' => []]);

        $bootloader = new HttpBootloader($configs);
        $bootloader->addInputBag('test', ['class' => 'foo', 'source' => 'bar']);

        $this->assertSame([
            'test' => ['class' => 'foo', 'source' => 'bar']
        ], $configs->getConfig(HttpConfig::CONFIG)['inputBags']);
    }

    /** @dataProvider middlewaresDataProvider */
    public function testAddMiddleware(mixed $middleware): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(HttpConfig::CONFIG, ['middleware' => []]);

        $bootloader = new HttpBootloader($configs);
        $bootloader->addMiddleware($middleware);

        $this->assertSame([$middleware], $configs->getConfig(HttpConfig::CONFIG)['middleware']);
    }

    public function middlewaresDataProvider(): \Traversable
    {
        yield ['class-string'];
        yield [new class () implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
            }
        }];
        yield [new Autowire('class-string')];
    }
}
