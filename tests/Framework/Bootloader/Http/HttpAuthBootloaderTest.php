<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\Session\TokenStorage as SessionTokenStorage;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Auth\TokenStorageProviderInterface;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\BinderInterface;
use Spiral\Framework\Spiral;
use Spiral\Http\CurrentRequest;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

use function PHPUnit\Framework\assertInstanceOf;

final class HttpAuthBootloaderTest extends BaseTestCase
{
    public function testTokenStorageProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TokenStorageProviderInterface::class, TokenStorageProvider::class);
    }

    public function testTokenStorageInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TokenStorageInterface::class, SessionTokenStorage::class);
    }

    public function testProxyAuthContextInterfaceBinding(): void
    {
        $this->assertContainerBound(AuthContextInterface::class, AuthContextInterface::class);
    }

    #[TestScope(Spiral::Http)]
    public function testAuthContextInterfaceBinding(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getAttribute')
            ->willReturn(new AuthContext($this->createMock(ActorProviderInterface::class)));

        $currentRequest = new CurrentRequest();
        $currentRequest->set($request);
        $this->getContainer()->bindSingleton(CurrentRequest::class, $currentRequest);

        $this->assertContainerBound(AuthContextInterface::class, AuthContext::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigHasFragments(AuthConfig::CONFIG, [
            'defaultStorage'   => 'session',
            'defaultTransport' => 'cookie',
            'storages'         => ['session' => SessionTokenStorage::class],
        ]);

        /** @var array{header: HeaderTransport, cookie: CookieTransport} $config */
        $config = $this->getContainer()->get(AuthConfig::class)['transports'] ?? [];
        $this->assertArrayHasKey('cookie', $config);
        $this->assertArrayHasKey('header', $config);

        $this->assertInstanceOf(HeaderTransport::class, $config['header']);
        $this->assertInstanceOf(CookieTransport::class, $config['cookie']);
    }

    public function testCorrectDefaultTransports(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));

        $bootloader = new HttpAuthBootloader($configs);
        $bootloader->init(
            $this->getContainer()->get(EnvironmentInterface::class),
            $this->getContainer()->get(BinderInterface::class),
        );

        /** @var HeaderTransport $headerTransport */
        $headerTransport = $configs->getConfig(AuthConfig::CONFIG)['transports']['header'];
        $this->assertInstanceOf(HeaderTransport::class, $headerTransport);
        $header = (new \ReflectionClass($headerTransport))->getProperty('header');
        $this->assertSame('X-Auth-Token', $header->getValue($headerTransport));

        /** @var CookieTransport $cookieTransport */
        $cookieTransport = $configs->getConfig(AuthConfig::CONFIG)['transports']['cookie'];
        $this->assertInstanceOf(CookieTransport::class, $cookieTransport);
        $cookie = (new \ReflectionClass($cookieTransport))->getProperty('cookie');
        $this->assertSame('token', $cookie->getValue($cookieTransport));
    }

    public function testCorrectConfigurableTransports(): void
    {
        $loader = new class() implements LoaderInterface {
            public function has(string $section): bool
            {
                return $section === AuthConfig::CONFIG;
            }

            public function load(string $section): array
            {
                return [
                    'defaultTransport' => 'header',
                    'defaultStorage'   => 'session',
                    'storages'         => ['session' => SessionTokenStorage::class],
                    'transports'       => [
                        'header' => new HeaderTransport(header: 'X-Auth-Token-test'),
                        'cookie' => new CookieTransport(cookie: 'token-test', basePath: '/'),
                    ],
                ];
            }
        };
        $configs = new ConfigManager($loader);

        $bootloader = new HttpAuthBootloader($configs);
        $bootloader->init($this->getContainer()->get(EnvironmentInterface::class),
            $this->getContainer()->get(BinderInterface::class),
        );

        /** @var HeaderTransport $headerTransport */
        $headerTransport = $configs->getConfig(AuthConfig::CONFIG)['transports']['header'];
        $this->assertInstanceOf(HeaderTransport::class, $headerTransport);
        $header = (new \ReflectionClass($headerTransport))->getProperty('header');
        $this->assertSame('X-Auth-Token-test', $header->getValue($headerTransport));

        /** @var CookieTransport $cookieTransport */
        $cookieTransport = $configs->getConfig(AuthConfig::CONFIG)['transports']['cookie'];
        $this->assertInstanceOf(CookieTransport::class, $cookieTransport);
        $cookie = (new \ReflectionClass($cookieTransport))->getProperty('cookie');
        $this->assertSame('token-test', $cookie->getValue($cookieTransport));
    }

    public function testAddTokenStorage(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(AuthConfig::CONFIG, ['storages' => []]);

        $bootloader = new HttpAuthBootloader($configs, $this->getContainer());
        $bootloader->addTokenStorage('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], $configs->getConfig(AuthConfig::CONFIG)['storages']);
    }

    public function testAddTransport(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(AuthConfig::CONFIG, ['transports' => []]);

        $bootloader = new HttpAuthBootloader($configs, $this->getContainer());
        $bootloader->addTransport('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], $configs->getConfig(AuthConfig::CONFIG)['transports']);
    }
}
