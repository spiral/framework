<?php

declare(strict_types=1);

namespace Framework\Bootloader\Prototype;

use Spiral\App\SomeService\Client;
use Spiral\App\SomeService\HttpClient;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

final class PrototypeBootloaderTest extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->beforeBooting(function (PrototypeBootloader $bootloader) {
            $bootloader->bindProperty(
                'service.client.http',
                HttpClient::class
            );
        });

        parent::setUp();
    }

    public function testDependencies(): void
    {
        $this->assertBootloaderRegistered(CoreBootloader::class);
        $this->assertBootloaderRegistered(TokenizerListenerBootloader::class);
        $this->assertBootloaderRegistered(AttributesBootloader::class);
    }

    public function testPrototypeRegistryBinding(): void
    {
        $this->assertContainerBoundAsSingleton(
            PrototypeRegistry::class,
            PrototypeRegistry::class
        );
    }

    public function testPrototypedClassesShouldBeFound(): void
    {
        $registry = $this->getContainer()->get(PrototypeRegistry::class);

        $this->assertSame(Client::class, $registry->resolveProperty('service.client')->type->fullName);
        $this->assertSame(HttpClient::class, $registry->resolveProperty('service.client.http')->type->fullName);
    }
}
