<?php

declare(strict_types=1);

namespace Spiral\Encrypter;

use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Key;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Exception\EncrypterException;

/**
 * Only manages encrypter injections (factory).
 *
 * @implements InjectorInterface<EncrypterInterface>
 */
#[Singleton]
final class EncrypterFactory implements InjectorInterface, EncryptionInterface
{
    public function __construct(
        private readonly EncrypterConfig $config
    ) {
    }

    /**
     * @codeCoverageIgnore
     */
    public function generateKey(): string
    {
        try {
            return Key::createNewRandomKey()->saveToAsciiSafeString();
        } catch (CryptoException $e) {
            throw new EncrypterException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getKey(): string
    {
        try {
            Key::loadFromAsciiSafeString($this->config->getKey());
        } catch (CryptoException $e) {
            throw new EncrypterException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->config->getKey();
    }

    public function getEncrypter(): EncrypterInterface
    {
        return new Encrypter($this->getKey());
    }

    public function createInjection(\ReflectionClass $class, string $context = null): EncrypterInterface
    {
        return $this->getEncrypter();
    }
}
