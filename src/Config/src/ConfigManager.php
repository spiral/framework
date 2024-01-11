<?php

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Config\Exception\ConfigDeliveredException;
use Spiral\Config\Exception\PatchException;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ConfiguratorException;

/**
 * Load config files, provides container injection and modifies config data on
 * bootloading.
 *
 * @implements ConfiguratorInterface<object>
 */
#[Singleton]
final class ConfigManager implements ConfiguratorInterface
{
    private array $data = [];
    private array $defaults = [];
    private array $instances = [];

    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly bool $strict = true
    ) {
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

    public function exists(string $section): bool
    {
        return isset($this->defaults[$section]) || isset($this->data[$section]) || $this->loader->has($section);
    }

    public function setDefaults(string $section, array $data): void
    {
        if (isset($this->defaults[$section])) {
            throw new ConfiguratorException(\sprintf('Unable to set default config `%s` more than once.', $section));
        }

        if (isset($this->data[$section])) {
            throw new ConfigDeliveredException(
                \sprintf('Unable to set default config `%s`, config has been loaded.', $section)
            );
        }

        $this->defaults[$section] = $data;
    }

    public function modify(string $section, PatchInterface $patch): array
    {
        if (isset($this->instances[$section])) {
            if ($this->strict) {
                throw new ConfigDeliveredException(
                    \sprintf('Unable to patch config `%s`, config object has already been delivered.', $section)
                );
            }

            unset($this->instances[$section]);
        }

        $data = $this->getConfig($section);

        try {
            return $this->data[$section] = $patch->patch($data);
        } catch (PatchException $e) {
            throw new PatchException(\sprintf('Unable to modify config `%s`.', $section), $e->getCode(), $e);
        }
    }

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

            $data = \array_merge($this->defaults[$section], $data);
        } else {
            $data = $this->loader->load($section);
        }

        return $this->data[$section] = $data;
    }

    public function createInjection(\ReflectionClass $class, string $context = null): object
    {
        $config = $class->getConstant('CONFIG');
        if (isset($this->instances[$config])) {
            return $this->instances[$config];
        }

        return $this->instances[$config] = $class->newInstance($this->getConfig($config));
    }
}
