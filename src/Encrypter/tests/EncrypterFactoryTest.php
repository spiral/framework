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

#[\PHPUnit\Framework\Attributes\CoversClass(\Spiral\Encrypter\EncrypterFactory::class)]
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
            new EncrypterConfig(['key' => $key]),
        );

        self::assertInstanceOf(EncrypterInterface::class, $container->get(EncrypterInterface::class));

        self::assertInstanceOf(Encrypter::class, $container->get(EncrypterInterface::class));

        $encrypter = $container->get(EncrypterInterface::class);
        self::assertSame($key, $encrypter->getKey());
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
            new EncrypterConfig(['key' => $key]),
        );

        self::assertInstanceOf(EncryptionInterface::class, $container->get(EncryptionInterface::class));

        self::assertInstanceOf(EncrypterFactory::class, $container->get(EncryptionInterface::class));

        $encrypter = $container->get(EncryptionInterface::class)->getEncrypter();
        self::assertSame($key, $encrypter->getKey());
        self::assertSame($key, $container->get(EncryptionInterface::class)->getKey());
    }

    public function testExceptionKey(): void
    {
        $this->expectException(EncrypterException::class);

        $config = new EncrypterConfig([]);

        $factory = new EncrypterFactory($config);
        echo $factory->getKey();
    }

    public function testGenerateKey(): void
    {
        $key = Key::CreateNewRandomKey()->saveToAsciiSafeString();

        $manager = new EncrypterFactory(new EncrypterConfig([
            'key' => $key,
        ]));

        self::assertNotSame($key, $manager->generateKey());
    }
}
