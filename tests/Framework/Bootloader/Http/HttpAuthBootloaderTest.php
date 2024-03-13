<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\Config\AuthConfig;
use Spiral\Auth\Session\TokenStorage;
use Spiral\Auth\Session\TokenStorage as SessionTokenStorage;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProvider;
use Spiral\Auth\TokenStorageProviderInterface;
use Spiral\Bootloader\Auth\HttpAuthBootloader;
use Spiral\Config\LoaderInterface;
use Spiral\Config\ConfigManager;
use Spiral\Framework\Spiral;
use Spiral\Http\CurrentRequest;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class HttpAuthBootloaderTest extends BaseTestCase
{
    public function testTokenStorageProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TokenStorageProviderInterface::class, TokenStorageProvider::class);
    }

    public function testTokenStorageInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TokenStorageInterface::class, TokenStorage::class);
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
            'defaultStorage' => 'session',
            'defaultTransport' => 'cookie',
            'storages' => [
                'session' => SessionTokenStorage::class,
            ],
        ]);
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
