<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo\Aws;

use League\Flysystem\Visibility;
use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\FileSystemInfo\ClassBasedInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo\OptionsBasedInterface;
use Spiral\Storage\Config\DTO\Traits\OptionsTrait;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Config\DTO\Traits\ClassBasedTrait;

class AwsVisibilityConverter implements ClassBasedInterface, OptionsBasedInterface
{
    use ClassBasedTrait;
    use OptionsTrait;

    public const VISIBILITY_KEY = 'visibility';

    private $converter = null;

    /**
     * @param array $info
     *
     * @throws StorageException
     */
    public function __construct(array $info)
    {
        if (
            !array_key_exists(static::CLASS_KEY, $info)
            || !is_string($info[static::CLASS_KEY])
        ) {
            throw new ConfigException('Aws visibility converter must be described with class');
        }

        if (
            !array_key_exists(static::OPTIONS_KEY, $info)
            || empty($info[static::OPTIONS_KEY])
            || !is_array($info[static::OPTIONS_KEY])
        ) {
            throw new ConfigException('Aws visibility converter must be described with options list');
        }

        $this->setClass($info[static::CLASS_KEY], $info[static::CLASS_KEY]);

        $this->options = $info[static::OPTIONS_KEY];

        if (!$this->hasOption(static::VISIBILITY_KEY)) {
            throw new ConfigException(
                \sprintf('`%s` option should be defined for Aws visibility converter', static::VISIBILITY_KEY)
            );
        }

        $allowedVisibilityOptionValues = [Visibility::PUBLIC, Visibility::PRIVATE];
        if (!in_array($this->getOption(static::VISIBILITY_KEY), $allowedVisibilityOptionValues, true)) {
            throw new ConfigException(
                \sprintf(
                    '`%s` should be defined with one of values: %s',
                    static::VISIBILITY_KEY,
                    implode(',', $allowedVisibilityOptionValues)
                )
            );
        }
    }

    /**
     * Get aws visibility converter of provided class
     *
     * @return mixed|null
     */
    public function getConverter()
    {
        $class = $this->getClass();

        if (!$this->converter instanceof $class) {
            $this->converter = new $class($this->getOption(static::VISIBILITY_KEY));
        }

        return $this->converter;
    }
}
