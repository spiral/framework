<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Encrypter;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Encrypter\Exception\DecryptException;
use Spiral\Encrypter\Exception\EncrypterException;
use Spiral\Encrypter\Exception\EncryptException;

/**
 * Default implementation of spiral encrypter. Facade at top of defuse/php-encryption
 *
 * @see https://github.com/defuse/php-encryption
 */
final class Encrypter implements EncrypterInterface, InjectableInterface
{
    public const INJECTOR = EncrypterFactory::class;

    /** @var Key */
    private $key = null;

    /**
     * @param string $key Loads a Key from its encoded form (ANSI).
     */
    public function __construct(string $key)
    {
        try {
            $this->key = Key::loadFromAsciiSafeString($key);
        } catch (CryptoException $e) {
            throw new EncrypterException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withKey(string $key): EncrypterInterface
    {
        $encrypter = clone $this;
        try {
            $encrypter->key = Key::loadFromAsciiSafeString($key);
        } catch (CryptoException $e) {
            throw new EncrypterException($e->getMessage(), $e->getCode(), $e);
        }

        return $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        try {
            return $this->key->saveToAsciiSafeString();
        } catch (EnvironmentIsBrokenException $e) {
            throw new EncrypterException($e->getMessage(), $e->getCode(), $e);
        }
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
            return base64_encode(Crypto::Encrypt($packed, $this->key));
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            throw new DecryptException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
