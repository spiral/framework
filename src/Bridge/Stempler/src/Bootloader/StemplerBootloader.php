<?php

declare(strict_types=1);

namespace Spiral\Stempler\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container\Autowire;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Stempler\Directive;
use Spiral\Stempler\Processor\NullLocaleProcessor;
use Spiral\Stempler\StemplerCache;
use Spiral\Stempler\StemplerEngine;
use Spiral\Stempler\Transform\Finalizer;
use Spiral\Stempler\Transform\Visitor;
use Spiral\Stempler\VisitorInterface;
use Spiral\Translator\Views\LocaleProcessor;
use Spiral\Views\Bootloader\ViewsBootloader;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Processor;
use Spiral\Views\ProcessorInterface;

/**
 * Initiates stempler engine, it's cache and directives.
 */
#[Singleton]
final class StemplerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        StemplerEngine::class => [self::class, 'stemplerEngine'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(ContainerInterface $container, ViewsBootloader $views): void
    {
        $this->config->setDefaults(
            StemplerConfig::CONFIG,
            [
                'directives' => [
                    Directive\PHPDirective::class,
                    Directive\RouteDirective::class,
                    Directive\LoopDirective::class,
                    Directive\JsonDirective::class,
                    Directive\ConditionalDirective::class,
                    Directive\ContainerDirective::class,
                ],
                'processors' => [
                    Processor\ContextProcessor::class,
                ],
                'visitors' => [
                    Builder::STAGE_PREPARE => [
                        Visitor\DefineBlocks::class,
                        Visitor\DefineAttributes::class,
                        Visitor\DefineHidden::class,
                    ],
                    Builder::STAGE_TRANSFORM => [

                    ],
                    Builder::STAGE_FINALIZE => [
                        Visitor\DefineStacks::class,
                        Finalizer\StackCollector::class,
                    ],
                    Builder::STAGE_COMPILE => [
                    ],
                ],
            ]
        );

        $views->addEngine(StemplerEngine::class);

        if ($container->has(LocaleProcessor::class)) {
            $this->addProcessor(LocaleProcessor::class);
        } else {
            $this->addProcessor(NullLocaleProcessor::class);
        }
    }

    public function addDirective(string|Autowire|Directive\DirectiveRendererInterface $directive): void
    {
        $this->config->modify(
            StemplerConfig::CONFIG,
            new Append('directives', null, $directive)
        );
    }

    public function addProcessor(string|Autowire|ProcessorInterface $processor): void
    {
        $this->config->modify(
            StemplerConfig::CONFIG,
            new Append('processors', null, $processor)
        );
    }

    public function addVisitor(string|Autowire|VisitorInterface $visitor, int $stage = Builder::STAGE_COMPILE): void
    {
        $this->config->modify(
            StemplerConfig::CONFIG,
            new Append('visitors.' . $stage, null, $visitor)
        );
    }

    protected function stemplerEngine(
        #[Proxy] ContainerInterface $container,
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
