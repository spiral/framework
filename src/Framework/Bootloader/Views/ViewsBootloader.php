<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Views;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewLoader;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

final class ViewsBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        ViewsInterface::class => ViewManager::class,
        LoaderInterface::class => ViewLoader::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param EnvironmentInterface $env
     * @param DirectoriesInterface $dirs
     */
    public function boot(EnvironmentInterface $env, DirectoriesInterface $dirs): void
    {
        if (!$dirs->has('views')) {
            $dirs->set('views', $dirs->get('app') . 'views');
        }

        // default view config
        $this->config->setDefaults(
            'views',
            [
                'cache'        => [
                    'enabled'   => $env->get('VIEW_CACHE', !$env->get('DEBUG', false)),
                    'directory' => $dirs->get('cache') . 'views',
                ],
                'namespaces'   => [
                    'default' => [$dirs->get('views')],
                ],
                'dependencies' => [],
                'engines'      => [NativeEngine::class],
            ]
        );
    }

    /**
     * @param string $namespace
     * @param string $directory
     */
    public function addDirectory(string $namespace, string $directory): void
    {
        if (!isset($this->config->getConfig('views')['namespaces'][$namespace])) {
            $this->config->modify('views', new Append('namespaces', $namespace, []));
        }

        $this->config->modify('views', new Append('namespaces.' . $namespace, null, $directory));
    }

    /**
     * @param mixed $engine
     */
    public function addEngine($engine): void
    {
        $this->config->modify('views', new Append('engines', null, $engine));
    }

    /**
     * @param mixed $dependency
     */
    public function addCacheDependency($dependency): void
    {
        $this->config->modify('views', new Append('dependencies', null, $dependency));
    }
}
