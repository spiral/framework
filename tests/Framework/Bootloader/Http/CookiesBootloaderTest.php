<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Bootloader\Http\CookiesBootloader;
use Spiral\Bootloader\Http\Exception\ContextualObjectNotFoundException;
use Spiral\Bootloader\Http\Exception\InvalidRequestScopeException;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\CookieQueue;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class CookiesBootloaderTest extends BaseTestCase
{
    #[TestScope(Spiral::Http)]
    public function testCookieQueueBinding(): void
    {
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->with(CookieQueue::ATTRIBUTE)
            ->andReturn(new CookieQueue(), new CookieQueue(), new CookieQueue(), new CookieQueue());

        $this->assertContainerBound(CookieQueue::class);
        // Makes 3 calls to the container
        $this->assertContainerBoundNotAsSingleton(CookieQueue::class, CookieQueue::class);
    }

    #[TestScope(Spiral::Http)]
    public function testCookieQueueBindingWithoutCookieQueueInRequest(): void
    {
        $request = $this->mockContainer(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')
            ->with(CookieQueue::ATTRIBUTE)
            ->andReturn(null);

        $this->expectException(ContextualObjectNotFoundException::class);

        $this->getContainer()->get(CookieQueue::class);
    }

    #[TestScope(Spiral::Http)]
    public function testCookieQueueBindingWithoutRequest(): void
    {
        $this->expectException(InvalidRequestScopeException::class);

        $this->getContainer()->get(CookieQueue::class);
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

        $bootloader = new CookiesBootloader($configs, $this->getContainer());
        $bootloader->whitelistCookie('foo');

        $this->assertSame(['foo'], $configs->getConfig(CookiesConfig::CONFIG)['excluded']);
    }
}
