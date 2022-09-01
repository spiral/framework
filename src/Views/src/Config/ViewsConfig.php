<?php

declare(strict_types=1);

namespace Spiral\Views\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Views\Engine\Native\NativeEngine;

final class ViewsConfig extends InjectableConfig
{
    public const CONFIG = 'views';

    protected array $config = [
        'cache' => [
            'enable' => false,
            'directory' => '/tmp',
        ],
        'namespaces' => [],
        'dependencies' => [],
        'engines' => [
            NativeEngine::class,
        ],
        'globalVariables' => [],
    ];

    public function getGlobalVariables(): array
    {
        return (array) ($this->config['globalVariables'] ?? []);
    }

    public function isCacheEnabled(): bool
    {
        return !empty($this->config['cache']['enable']) || !empty($this->config['cache']['enabled']);
    }

    public function getCacheDirectory(): string
    {
        return \rtrim($this->config['cache']['directory'] ?? '', '/') . '/';
    }

    /**
     * Return all namespaces and their associated directories.
     */
    public function getNamespaces(): array
    {
        return (array) ($this->config['namespaces'] ?? []);
    }

    /**
     * Class names of all view dependencies.
     *
     * @return array<int, Autowire>
     */
    public function getDependencies(): array
    {
        return \array_map(
            fn (mixed $dependency): Autowire =>  $this->wire($dependency),
            (array) ($this->config['dependencies'] ?? [])
        );
    }

    /**
     * Get all the engines associated with view component.
     *
     * @return array<int, Autowire>
     */
    public function getEngines(): array
    {
        return \array_map(
            fn (mixed $engine): Autowire =>  $this->wire($engine),
            (array) ($this->config['engines'] ?? [])
        );
    }

    /**
     * @param Autowire|class-string $item
     */
    private function wire(Autowire|string $item): Autowire
    {
        if ($item instanceof Autowire) {
            return $item;
        }

        return new Autowire($item);
    }
}
