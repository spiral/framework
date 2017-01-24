<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Encrypter;

use Defuse\Crypto\Key;
use Spiral\Encrypter\Configs\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncrypterManager;
use Spiral\Tests\BaseTest;

class EncryptionTest extends BaseTest
{
    public function testImmutable()
    {
        $encrypter = new Encrypter($keyA = Key::CreateNewRandomKey()->saveToAsciiSafeString());
        $new = $encrypter->withKey($keyB = Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $this->assertNotSame($encrypter, $new);

        $this->assertEquals($keyA, $encrypter->getKey());
        $this->assertEquals($keyB, $new->getKey());

    }

    public function testEncryption()
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

    /**
     * @expectedException \Spiral\Encrypter\Exceptions\DecryptException
     */
    public function testBadData()
    {
        $encrypter = new Encrypter(Key::CreateNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter->decrypt('badData.' . $encrypted);
    }

    public function testGetInstance()
    {
        /** @var EncrypterManager $manager */
        $manager = $this->app->container->get(EncrypterManager::class);

        $this->app->container->bind(EncrypterConfig::class, new EncrypterConfig([
            'key' => base64_encode($key = $manager->generateKey())
        ]));

        //Recreate singleton
        $this->app->container->removeBinding(EncrypterManager::class);

        $this->assertInstanceOf(EncrypterInterface::class, $this->app->encrypter);
        $this->assertInstanceOf(Encrypter::class, $this->app->encrypter);

        $this->assertSame($key, $this->app->encrypter->getKey());
    }
}