<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Encrypter;

use Spiral\Components\Encrypter\Encrypter;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class EncryptionTest extends TestCase
{
    public function testEncryption()
    {
        $encrypter = $this->createEncrypter();

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter->setKey('0987654321123456');
        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypter->setCipher(MCRYPT_RIJNDAEL_256);
        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));
    }

    /**
     * @expectedException \Spiral\Components\Encrypter\DecryptionException
     * @expectedExceptionMessage Unable to unpack provided data.
     */
    public function testBadData()
    {
        $encrypter = $this->createEncrypter();

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));
        $encrypter->decrypt('abc' . $encrypted);
    }

    /**
     * @expectedException \Spiral\Components\Encrypter\DecryptionException
     * @expectedExceptionMessage Encrypted data does not have valid signature.
     */
    public function testBadSignature()
    {
        $encrypter = $this->createEncrypter();

        $encrypted = $encrypter->encrypt('test string');
        $this->assertNotEquals('test string', $encrypted);
        $this->assertEquals('test string', $encrypter->decrypt($encrypted));

        $encrypted = base64_decode(str_replace(array('-', '_', '~'), array('+', '/', '='), $encrypted));
        $encrypted = json_decode($encrypted, true);
        $encrypted[Encrypter::SIGNATURE] = 'BADONE';

        $encrypted = str_replace(array('+', '/', '='), array('-', '_', '~'), base64_encode(json_encode($encrypted)));
        $encrypter->decrypt($encrypted);
    }

    protected function createEncrypter($config = array('key' => '1234567890123456'))
    {
        return new Encrypter(MemoryCore::getInstance()->setConfig('encrypter', $config));
    }
}