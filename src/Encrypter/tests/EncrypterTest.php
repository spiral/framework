<?php

declare(strict_types=1);

namespace Spiral\Tests\Encrypter;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\Exception\DecryptException;
use Spiral\Encrypter\Exception\EncrypterException;

class EncrypterTest extends TestCase
{
    public function testImmutable(): void
    {
        $encrypter = new Encrypter($keyA = Key::CreateNewRandomKey()->saveToAsciiSafeString());
        $new = $encrypter->withKey($keyB = Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $this->assertNotSame($encrypter, $new);

        $this->assertEquals($keyA, $encrypter->getKey());
        $this->assertEquals($keyB, $new->getKey());
    }

    /**
     * @covers \Spiral\Encrypter\Encrypter::encrypt
     */
    public function testEncryption(): void
    {
        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter = $encrypter->withKey(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));
    }

    public function testBadData(): void
    {
        $this->expectException(DecryptException::class);

        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter->decrypt('badData.' . $encrypted);
    }

    public function testBadKey(): void
    {
        $this->expectException(EncrypterException::class);

        $encrypter = new Encrypter('bad-key');
    }

    public function testBadWithKey(): void
    {
        $this->expectException(EncrypterException::class);

        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());
        $encrypter = $encrypter->withKey('bad-key');
    }
}
