<?php

declare(strict_types=1);

namespace Framework\Bootloader\Security;

use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class EncrypterBootloaderTest extends BaseTestCase
{
    public const ENV = [
        'ENCRYPTER_KEY' => 'def000004894d79b1669d0c254164b501a1894bec1a65c4486af413d741e1c3d07fba4124a6c3b9f9a4a5253f667f1494a62c5976e0628de4f31f62651108e7b0c42fe1c',
    ];

    public function testEncryptionInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(EncryptionInterface::class, EncrypterFactory::class);
    }

    public function testEncrypterInterfaceBinding(): void
    {
        $this->assertContainerBound(EncrypterInterface::class, Encrypter::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(EncrypterConfig::CONFIG, [
            'key' => self::ENV['ENCRYPTER_KEY'],
        ]);
    }
}
