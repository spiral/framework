<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo;

use Spiral\Storage\Exception\ConfigException;
use Spiral\Storage\Config\DTO\Traits\OptionsTrait;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Config\DTO\Traits\ClassBasedTrait;

abstract class FileSystemInfo implements FileSystemInfoInterface, ClassBasedInterface, OptionsBasedInterface
{
    use ClassBasedTrait;
    use OptionsTrait;

    protected const REQUIRED_OPTIONS = [];

    protected const ADDITIONAL_OPTIONS = [];

    protected const FILE_SYSTEM_INFO_TYPE = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $resolver;

    /**
     * @param string $name
     * @param array $info
     *
     * @throws StorageException
     */
    public function __construct(string $name, array $info)
    {
        $this->validateInfoSufficient($name, $info);

        $this->name = $name;

        $this->checkClass($info[static::ADAPTER_KEY], \sprintf('Filesystem %s adapter', $this->name));
        $this->adapter = $info[static::ADAPTER_KEY];

        if (array_key_exists(static::RESOLVER_KEY, $info)) {
            $this->checkClass($info[static::RESOLVER_KEY], \sprintf('Filesystem %s resolver', $this->name));
            $this->resolver = $info[static::RESOLVER_KEY];
        }

        $this->prepareOptions($info[OptionsBasedInterface::OPTIONS_KEY]);

        if ($this instanceof SpecificConfigurableFileSystemInfo) {
            $this->constructSpecific($info);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAdapterClass(): string
    {
        return $this->adapter;
    }

    /**
     * @inheritDoc
     */
    public function getResolverClass(): string
    {
        return $this->resolver;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isAdvancedUsage(): bool
    {
        foreach (static::ADDITIONAL_OPTIONS as $optionalOption => $type) {
            if ($this->hasOption($optionalOption)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate and prepare options
     *
     * @param array $options
     *
     * @throws ConfigException
     */
    protected function prepareOptions(array $options): void
    {
        $this->validateRequiredOptions(
            array_keys(static::REQUIRED_OPTIONS),
            $options,
            \sprintf(' for filesystem `%s`', $this->getName())
        );

        foreach ($options as $optionKey => $option) {
            if (($type = $this->getOptionType($optionKey)) === null) {
                continue;
            }

            $this->validateOptionByType($optionKey, $type, $option);

            $this->options[$optionKey] = $this->processOptionByType($option, $type);
        }
    }

    /**
     * Validate if description contains all required options
     *
     * @param string $fs
     * @param array $info
     *
     * @throws ConfigException
     */
    protected function validateInfoSufficient(string $fs, array $info): void
    {
        if (!array_key_exists(static::ADAPTER_KEY, $info)) {
            throw new ConfigException(
                \sprintf('Filesystem `%s` needs adapter class defined', $fs)
            );
        }

        if (!array_key_exists(OptionsBasedInterface::OPTIONS_KEY, $info)) {
            throw new ConfigException(
                \sprintf('Filesystem `%s` needs options defined', $fs)
            );
        }
    }

    /**
     * Validate option type is correct
     *
     * @param string $optionLabel
     * @param string $optionType
     * @param $optionVal
     *
     * @throws ConfigException
     */
    protected function validateOptionByType(string $optionLabel, string $optionType, $optionVal): void
    {
        if (!$this->isOptionHasRequiredType($optionLabel, $optionVal, $optionType)) {
            throw new ConfigException(
                \sprintf(
                    'Option `%s` defined in wrong format for filesystem `%s`, %s expected',
                    $optionLabel,
                    $this->getName(),
                    $optionType
                )
            );
        }
    }

    /**
     * Get expected option type by label
     *
     * @param string $option
     *
     * @return string|null
     */
    protected function getOptionType(string $option): ?string
    {
        if (array_key_exists($option, static::REQUIRED_OPTIONS)) {
            return static::REQUIRED_OPTIONS[$option];
        }

        return static::ADDITIONAL_OPTIONS[$option] ?? null;
    }
}
