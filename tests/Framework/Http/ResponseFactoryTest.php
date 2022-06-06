<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Tests\Framework\BaseTest;

final class ResponseFactoryTest extends BaseTest
{
    public function testDefaultBaseHeaders(): void
    {
        $app = $this->makeApp();

        /** @var ResponseFactoryInterface $factory */
        $factory = $app->get(ResponseFactoryInterface::class);

        $response = $factory->createResponse(200, 'test');

        $this->assertSame(['Content-Type' => ['text/html; charset=UTF-8']], $response->getHeaders());
    }

    public function testAddBaseHeaders(): void
    {
        $app = $this->makeApp();
        $app->getContainer()->bind(
            HttpConfig::class,
            new HttpConfig(['headers' => ['test' => 'test', 'test2' => 'test2']])
        );

        /** @var ResponseFactoryInterface $factory */
        $factory = $app->get(ResponseFactoryInterface::class);

        $response = $factory->createResponse(200, 'test');

        $this->assertSame(['test' => ['test'], 'test2' => ['test2']], $response->getHeaders());
    }
}
