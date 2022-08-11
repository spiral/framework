<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\ConfigDeliveredException;
use Spiral\Config\Exception\PatchException;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ConfiguratorException;

/**
 * Load config files, provides container injection and modifies config data on
 * bootloading.
 */
final class ConfigManager implements ConfiguratorInterface, SingletonInterface
{
    /** @var LoaderInterface */
    private $loader;

    /** @var bool */
    private $strict;

    /** @var array */
    private $data = [];

    /** @var array */
    private $defaults = [];

    /** @var array */
    private $instances = [];

    public function __construct(LoaderInterface $loader, bool $strict = true)
    {
        $this->loader = $loader;
        $this->strict = $strict;
    }

    /**
     * Clone state will reset both data and instance cache.
     */
    public function __clone()
    {
        $this->data = [];
        $this->defaults = [];
        $this->instances = [];
    }

    /**
     * @inheritdoc
     */
    public function exists(string $section): bool
    {
        return isset($this->defaults[$section]) || isset($this->data[$section]) || $this->loader->has($section);
    }

    /**
     * @inheritdoc
     */
    public function setDefaults(string $section, array $data): void
    {
        if (isset($this->defaults[$section])) {
            throw new ConfiguratorException("Unable to set default config `{$section}` more than once.");
        }

        if (isset($this->data[$section])) {
            throw new ConfigDeliveredException("Unable to set default config `{$section}`, config has been loaded.");
        }

        $this->defaults[$section] = $data;
    }

    /**
     * @inheritdoc
     */
    public function modify(string $section, PatchInterface $patch): array
    {
        if (isset($this->instances[$section])) {
            if ($this->strict) {
                throw new ConfigDeliveredException(
                    "Unable to patch config `{$section}`, config object has already been delivered."
                );
            }

            unset($this->instances[$section]);
        }

        $data = $this->getConfig($section);

        try {
            return $this->data[$section] = $patch->patch($data);
        } catch (PatchException $e) {
            throw new PatchException("Unable to modify config `{$section}`.", $e->getCode(), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfig(string $section = null): array
    {
        if (isset($this->data[$section])) {
            return $this->data[$section];
        }

        if (isset($this->defaults[$section])) {
            $data = [];
            if ($this->loader->has($section)) {
                $data = $this->loader->load($section);
            }

            $data = array_merge($this->defaults[$section], $data);
        } else {
            $data = $this->loader->load($section);
        }

        return $this->data[$section] = $data;
    }

    /**
     * @inheritdoc
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        $config = $class->getConstant('CONFIG');
        if (isset($this->instances[$config])) {
            return $this->instances[$config];
        }

        return $this->instances[$config] = $class->newInstance($this->getConfig($config));
    }
}
