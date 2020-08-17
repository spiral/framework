<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Encrypter;

use Spiral\Encrypter\Exception\EncrypterException;

interface EncryptionInterface
{
    /**
     * Generate new random encryption key (binary format).
     *
     * @return string
     *
     * @throws EncrypterException
     */
    public function generateKey(): string;

    /**
     * @return string
     *
     * @throws EncrypterException
     */
    public function getKey(): string;

    /**
     * @return EncrypterInterface
     */
    public function getEncrypter(): EncrypterInterface;
}
