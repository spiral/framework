<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Encrypter;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Key;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Encrypter\Exceptions\DecryptException;
use Spiral\Encrypter\Exceptions\EncryptException;

/**
 * Default implementation of spiral encrypter. Sugary implementation at top of defuse/php-encryption
 *
 * @see  https://github.com/defuse/php-encryption
 */
class Encrypter implements EncrypterInterface, InjectableInterface
{
    /**
     * Injector is dedicated to outer class since Encrypter is pretty simple.
     */
    const INJECTOR = EncrypterManager::class;

    /**
     * @var Key
     */
    private $key = null;

    /**
     * Encrypter constructor.
     *
     * @param string $key Loads a Key from its encoded form (ANSI).
     */
    public function __construct(string $key)
    {
        $this->key = Key::loadFromAsciiSafeString($key);
    }

    /**
     * {@inheritdoc}
     */
    public function withKey(string $key): EncrypterInterface
    {
        $encrypter = clone $this;
        $encrypter->key = Key::loadFromAsciiSafeString($key);

        return $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key->saveToAsciiSafeString();
    }

    /**
     * {@inheritdoc}
     *
     * Data encoded using json_encode method, only supported formats are allowed!
     */
    public function encrypt($data): string
    {
        $packed = json_encode($data);

        try {
            return base64_encode(
                Crypto::Encrypt($packed, $this->key)
            );
        } catch (BadFormatException $e) {
            throw new EncryptException($e->getMessage(), $e->getCode(), $e);
        } catch (CryptoException $e) {
            throw new EncryptException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * json_decode with assoc flag set to true
     */
    public function decrypt(string $payload)
    {
        try {
            $result = Crypto::Decrypt(
                base64_decode($payload),
                $this->key
            );

            return json_decode($result, true);
        } catch (CryptoException $e) {
            throw new DecryptException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
