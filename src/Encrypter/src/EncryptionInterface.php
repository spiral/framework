<?php

declare(strict_types=1);

namespace Spiral\Encrypter;

use Spiral\Encrypter\Exception\EncrypterException;

interface EncryptionInterface
{
    /**
     * Generate new random encryption key (binary format).
     *
     * @throws EncrypterException
     */
    public function generateKey(): string;

    /**
     * @throws EncrypterException
     */
    public function getKey(): string;

    public function getEncrypter(): EncrypterInterface;
}
