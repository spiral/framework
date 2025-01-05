<?php

declare(strict_types=1);

namespace Spiral\Tests\Encrypter;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\Exception\DecryptException;
use Spiral\Encrypter\Exception\EncrypterException;

#[\PHPUnit\Framework\Attributes\CoversClass(\Spiral\Encrypter\Encrypter::class)]
class EncrypterTest extends TestCase
{
    public function testImmutable(): void
    {
        $encrypter = new Encrypter($keyA = Key::CreateNewRandomKey()->saveToAsciiSafeString());
        $new = $encrypter->withKey($keyB = Key::CreateNewRandomKey()->saveToAsciiSafeString());

        self::assertNotSame($encrypter, $new);

        self::assertEquals($keyA, $encrypter->getKey());
        self::assertEquals($keyB, $new->getKey());
    }

    public function testEncryption(): void
    {
        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        self::assertNotSame('test string', $encrypted);
        self::assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter = $encrypter->withKey(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        self::assertNotSame('test string', $encrypted);
        self::assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypted = $encrypter->encrypt('test string');
        self::assertNotSame('test string', $encrypted);
        self::assertEquals('test string', $encrypter->decrypt($encrypted));
    }

    public function testBadData(): void
    {
        $this->expectException(DecryptException::class);

        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        self::assertNotSame('test string', $encrypted);
        self::assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter->decrypt('badData.' . $encrypted);
    }

    public function testBadKey(): void
    {
        $this->expectException(EncrypterException::class);

        new Encrypter('bad-key');
    }

    public function testBadWithKey(): void
    {
        $this->expectException(EncrypterException::class);

        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());
        $encrypter->withKey('bad-key');
    }
}
