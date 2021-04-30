<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\Traits;

use Spiral\Storage\Exception\StorageException;

/**
 * Trait for dto based on class usage
 */
trait ClassBasedTrait
{
    /**
     * @var string|null
     */
    protected $class = null;

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Set class for DTO and check if class exists
     *
     * @param string $class
     * @param string|null $exceptionMsg
     *
     * @return static
     *
     * @throws StorageException
     */
    public function setClass(string $class, ?string $exceptionMsg = null): self
    {
        $this->checkClass($class, $exceptionMsg ?: '');

        $this->class = $class;

        return $this;
    }

    /**
     * Check if class exists
     *
     * @param string $class
     * @param string $errorPostfix
     *
     * @throws StorageException
     */
    protected function checkClass(string $class, string $errorPostfix): void
    {
        if (!class_exists($class)) {
            throw new StorageException(
                \sprintf('Class `%s` not exists. %s', $class, $errorPostfix)
            );
        }
    }
}
