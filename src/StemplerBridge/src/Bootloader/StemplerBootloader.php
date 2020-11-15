<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Views\ViewsBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Stempler\Directive;
use Spiral\Stempler\StemplerCache;
use Spiral\Stempler\StemplerEngine;
use Spiral\Stempler\Transform\Finalizer;
use Spiral\Stempler\Transform\Visitor;
use Spiral\Stempler\VisitorInterface;
use Spiral\Translator\Views\LocaleProcessor;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Processor;
use Spiral\Views\ProcessorInterface;

/**
 * Initiates stempler engine, it's cache and directives.
 */
final class StemplerBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        ViewsBootloader::class
    ];

    protected const SINGLETONS = [
        StemplerEngine::class => [self::class, 'stemplerEngine']
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
     * @param ContainerInterface $container
     * @param ViewsBootloader    $views
     */
    public function boot(ContainerInterface $container, ViewsBootloader $views): void
    {
        $this->config->setDefaults(
            'views/stempler',
            [
                'directives' => [
                    Directive\PHPDirective::class,
                    Directive\RouteDirective::class,
                    Directive\LoopDirective::class,
                    Directive\JsonDirective::class,
                    Directive\ConditionalDirective::class,
                    Directive\ContainerDirective::class
                ],
                'processors' => [
                    Processor\ContextProcessor::class
                ],
                'visitors'   => [
                    Builder::STAGE_PREPARE   => [
                        Visitor\DefineBlocks::class,
                        Visitor\DefineAttributes::class,
                        Visitor\DefineHidden::class,
                    ],
                    Builder::STAGE_TRANSFORM => [

                    ],
                    Builder::STAGE_FINALIZE  => [
                        Visitor\DefineStacks::class,
                        Finalizer\StackCollector::class,
                    ],
                    Builder::STAGE_COMPILE   => [
                    ]
                ]
            ]
        );

        $views->addEngine(StemplerEngine::class);

        if ($container->has(LocaleProcessor::class)) {
            $this->addProcessor(LocaleProcessor::class);
        }
    }

    /**
     * @param string|Directive\DirectiveRendererInterface $directive
     */
    public function addDirective($directive): void
    {
        $this->config->modify(
            'views/stempler',
            new Append('directives', null, $directive)
        );
    }

    /**
     * @param mixed|ProcessorInterface $processor
     */
    public function addProcessor($processor): void
    {
        $this->config->modify(
            'views/stempler',
            new Append('processors', null, $processor)
        );
    }

    /**
     * @param string|VisitorInterface $visitor
     * @param int                     $stage
     */
    public function addVisitor($visitor, int $stage = Builder::STAGE_COMPILE): void
    {
        $this->config->modify(
            'views/stempler',
            new Append('visitors.' . (string)$stage, null, $visitor)
        );
    }

    /**
     * @param ContainerInterface $container
     * @param StemplerConfig     $config
     * @param ViewsConfig        $viewConfig
     * @return StemplerEngine
     */
    protected function stemplerEngine(
        ContainerInterface $container,
        StemplerConfig $config,
        ViewsConfig $viewConfig
    ): StemplerEngine {
        $cache = null;
        if ($viewConfig->isCacheEnabled()) {
            $cache = new StemplerCache($viewConfig->getCacheDirectory());
        }

        return new StemplerEngine($container, $config, $cache);
    }
}
