<?php

declare(strict_types=1);

namespace Spiral\Tests\Encrypter;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Encrypter\Exception\EncrypterException;

class EncrypterFactoryTest extends TestCase
{
    public function testInjection(): void
    {
        $key = Key::CreateNewRandomKey()->saveToAsciiSafeString();

        $container = new Container();
        $container->bind(EncrypterInterface::class, Encrypter::class);

        //Manager must be created automatically
        $container->bind(
            EncrypterConfig::class,
            new EncrypterConfig(['key' => $key])
        );

        $this->assertInstanceOf(
            EncrypterInterface::class,
            $container->get(EncrypterInterface::class)
        );

        $this->assertInstanceOf(Encrypter::class, $container->get(EncrypterInterface::class));

        $encrypter = $container->get(EncrypterInterface::class);
        $this->assertSame($key, $encrypter->getKey());
    }

    public function testGetEncrypter(): void
    {
        $key = Key::CreateNewRandomKey()->saveToAsciiSafeString();

        $container = new Container();
        $container->bind(EncrypterInterface::class, Encrypter::class);
        $container->bind(EncryptionInterface::class, EncrypterFactory::class);

        //Manager must be created automatically
        $container->bind(
            EncrypterConfig::class,
            new EncrypterConfig(['key' => $key])
        );

        $this->assertInstanceOf(
            EncryptionInterface::class,
            $container->get(EncryptionInterface::class)
        );

        $this->assertInstanceOf(
            EncrypterFactory::class,
            $container->get(EncryptionInterface::class)
        );

        $encrypter = $container->get(EncryptionInterface::class)->getEncrypter();
        $this->assertSame($key, $encrypter->getKey());
        $this->assertSame($key, $container->get(EncryptionInterface::class)->getKey());
    }

    public function testExceptionKey(): void
    {
        $this->expectException(EncrypterException::class);

        $config = new EncrypterConfig([]);

        $factory = new EncrypterFactory($config);
        echo $factory->getKey();
    }

    /**
     * @covers \Spiral\Encrypter\EncrypterFactory::generateKey
     */
    public function testGenerateKey(): void
    {
        $key = Key::CreateNewRandomKey()->saveToAsciiSafeString();

        $manager = new EncrypterFactory(new EncrypterConfig([
            'key' => $key,
        ]));

        $this->assertNotSame($key, $manager->generateKey());
    }
}
