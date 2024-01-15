<?php

declare(strict_types=1);

namespace Spiral\Views\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\DependencyInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\EngineInterface;
use Spiral\Views\GlobalVariables;
use Spiral\Views\GlobalVariablesInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewLoader;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

#[Singleton]
final class ViewsBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ViewsInterface::class => ViewManager::class,
        ViewManager::class => ViewManager::class,
        LoaderInterface::class => [self::class, 'initLoader'],
        GlobalVariablesInterface::class => [self::class, 'initGlobalVariables'],
        GlobalVariables::class => GlobalVariables::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env, DirectoriesInterface $dirs, DebugMode $debugMode): void
    {
        if (!$dirs->has('views')) {
            $dirs->set('views', $dirs->get('app') . 'views');
        }

        // default view config
        $this->config->setDefaults(
            ViewsConfig::CONFIG,
            [
                'cache' => [
                    'enabled' => $env->get('VIEW_CACHE', !$debugMode->isEnabled()),
                    'directory' => $dirs->get('cache') . 'views',
                ],
                'namespaces' => [
                    'default' => [$dirs->get('views')],
                ],
                'dependencies' => [],
                'engines' => [NativeEngine::class],
            ]
        );
    }

    public function addDirectory(string $namespace, string $directory): void
    {
        if (!isset($this->config->getConfig(ViewsConfig::CONFIG)['namespaces'][$namespace])) {
            $this->config->modify(ViewsConfig::CONFIG, new Append('namespaces', $namespace, []));
        }

        $this->config->modify(
            ViewsConfig::CONFIG,
            new Append('namespaces.' . $namespace, null, $directory)
        );
    }

    public function addEngine(string|EngineInterface $engine): void
    {
        $this->config->modify(
            ViewsConfig::CONFIG,
            new Append('engines', null, $engine)
        );
    }

    public function addCacheDependency(string|DependencyInterface $dependency): void
    {
        $this->config->modify(
            ViewsConfig::CONFIG,
            new Append('dependencies', null, $dependency)
        );
    }

    protected function initGlobalVariables(ViewsConfig $config): GlobalVariablesInterface
    {
        return new GlobalVariables(
            $config->getGlobalVariables()
        );
    }

    protected function initLoader(ViewsConfig $config): LoaderInterface
    {
        return new ViewLoader(
            $config->getNamespaces()
        );
    }
}
