<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Bootloader\Http\CookiesBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\CookieQueue;
use Spiral\Tests\Framework\BaseTestCase;

final class CookiesBootloaderTest extends BaseTestCase
{
    public function testCookieQueueBinding(): void
    {
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->once()
            ->with(CookieQueue::ATTRIBUTE, null)
            ->andReturn(new CookieQueue());

        $this->assertContainerBound(CookieQueue::class);
    }

    public function testCookieQueueBindingShouldThrowAndExceptionWhenAttributeIsEmpty(): void
    {
        $this->expectExceptionMessage('Unable to resolve CookieQueue, invalid request scope');
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->once()
            ->with(CookieQueue::ATTRIBUTE, null)
            ->andReturnNull();

        $this->assertContainerBound(CookieQueue::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(CookiesConfig::CONFIG, [
            'domain' => '.%s',
            'method' => CookiesConfig::COOKIE_ENCRYPT,
            'excluded' => ['PHPSESSID', 'csrf-token', 'sid'],
        ]);
    }

    public function testWhitelistCookie(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(CookiesConfig::CONFIG, ['excluded' => []]);

        $bootloader = new CookiesBootloader($configs);
        $bootloader->whitelistCookie('foo');

        $this->assertSame(['foo'], $configs->getConfig(CookiesConfig::CONFIG)['excluded']);
    }
}
