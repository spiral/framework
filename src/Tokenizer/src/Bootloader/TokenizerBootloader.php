<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Set;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\ClassLocatorInjector;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\EnumLocator;
use Spiral\Tokenizer\EnumLocatorInjector;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\InterfaceLocator;
use Spiral\Tokenizer\InterfaceLocatorInjector;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationLocatorInjector;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;
use Spiral\Tokenizer\ScopedEnumLocator;
use Spiral\Tokenizer\ScopedEnumsInterface;
use Spiral\Tokenizer\ScopedInterfaceLocator;
use Spiral\Tokenizer\ScopedInterfacesInterface;

#[Singleton]
final class TokenizerBootloader extends Bootloader
{
    protected const BINDINGS = [
        ScopedClassesInterface::class => ScopedClassLocator::class,
        ScopedEnumsInterface::class => ScopedEnumLocator::class,
        ScopedInterfacesInterface::class => ScopedInterfaceLocator::class,
        ClassesInterface::class => ClassLocator::class,
        EnumsInterface::class => EnumLocator::class,
        InterfacesInterface::class => InterfaceLocator::class,
        InvocationsInterface::class => InvocationLocator::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
    ) {
    }

    public function init(BinderInterface $binder, DirectoriesInterface $dirs, EnvironmentInterface $env): void
    {
        $binder->bindInjector(ClassLocator::class, ClassLocatorInjector::class);
        $binder->bindInjector(EnumLocator::class, EnumLocatorInjector::class);
        $binder->bindInjector(InterfaceLocator::class, InterfaceLocatorInjector::class);
        $binder->bindInjector(InvocationLocator::class, InvocationLocatorInjector::class);

        $this->config->setDefaults(
            TokenizerConfig::CONFIG,
            [
                'debug' => false,
                'directories' => [$dirs->get('app')],
                'exclude' => [
                    $dirs->get('resources'),
                    $dirs->get('config'),
                    'tests',
                    'migrations',
                ],
                'cache' => [
                    'directory' => $dirs->get('runtime') . 'cache/listeners',
                    'enabled' => \filter_var($env->get('TOKENIZER_CACHE_TARGETS', false), \FILTER_VALIDATE_BOOL),
                ],
                'load' => [
                    'classes' => \filter_var($env->get('TOKENIZER_LOAD_CLASSES', true), \FILTER_VALIDATE_BOOL),
                    'enums' => \filter_var($env->get('TOKENIZER_LOAD_ENUMS', false), \FILTER_VALIDATE_BOOL),
                    'interfaces' => \filter_var($env->get('TOKENIZER_LOAD_INTERFACES', false), \FILTER_VALIDATE_BOOL),
                ],
            ],
        );
    }

    /**
     * Add directory for indexation.
     */
    public function addDirectory(string $directory): void
    {
        $this->config->modify(
            TokenizerConfig::CONFIG,
            new Append('directories', null, $directory),
        );
    }

    /**
     * Add directory for indexation into specific scope.
     * @param non-empty-string $scope
     * @param non-empty-string $directory
     */
    public function addScopedDirectory(string $scope, string $directory): void
    {
        $this->ensureScopeExists($scope, 'directories');

        $this->config->modify(
            TokenizerConfig::CONFIG,
            new Append('scopes.' . $scope . '.directories', null, $directory),
        );
    }

    /**
     * Exclude directory from indexation in specific scope.
     * @param non-empty-string $scope
     * @param non-empty-string $directory
     */
    public function excludeScopedDirectory(string $scope, string $directory): void
    {
        $this->ensureScopeExists($scope, 'exclude');

        $this->config->modify(
            TokenizerConfig::CONFIG,
            new Append('scopes.' . $scope . '.exclude', null, $directory),
        );
    }

    private function ensureScopeExists(string $scope, string $section): void
    {
        if (!isset($this->config->getConfig(TokenizerConfig::CONFIG)['scopes'])) {
            $this->config->modify(
                TokenizerConfig::CONFIG,
                new Set('scopes', []),
            );
        }

        if (!isset($this->config->getConfig(TokenizerConfig::CONFIG)['scopes'][$scope])) {
            $this->config->modify(
                TokenizerConfig::CONFIG,
                new Append('scopes', $scope, [$section => []]),
            );
        }
    }
}
