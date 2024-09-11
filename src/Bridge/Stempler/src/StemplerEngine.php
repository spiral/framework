<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\DynamicRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Compiler\Renderer\PHPRenderer;
use Spiral\Stempler\Compiler\Result;
use Spiral\Stempler\Compiler\SourceMap;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Stempler\Directive\DirectiveGroup;
use Spiral\Stempler\Directive\DirectiveRendererInterface;
use Spiral\Stempler\Lexer\Grammar;
use Spiral\Stempler\Parser\Syntax;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Views\ContextInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Exception\CompileException;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;
use Throwable;

final class StemplerEngine implements EngineInterface
{
    // default file extension
    public const EXTENSION = 'dark.php';

    private string $classPrefix = '__StemplerView__';

    private ?Builder $builder = null;
    private ?LoaderInterface $loader = null;

    public function __construct(
        #[Proxy] private readonly ContainerInterface $container,
        private readonly StemplerConfig $config,
        private readonly ?StemplerCache $cache = null
    ) {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        $engine = clone $this;
        $engine->loader = $loader->withExtension(static::EXTENSION);
        $engine->builder = $engine->makeBuilder(new StemplerLoader($engine->loader, $this->getProcessors()));

        return $engine;
    }

    public function getLoader(): LoaderInterface
    {
        if ($this->loader === null) {
            throw new EngineException('No associated loader found');
        }

        return $this->loader;
    }

    /**
     * Return builder locked to specific context.
     */
    public function getBuilder(ContextInterface $context): Builder
    {
        if ($this->builder === null) {
            throw new EngineException('No associated builder found');
        }

        // since view source support pre-processing we must ensure that context is always set
        $loader = $this->builder->getLoader();
        if ($loader instanceof StemplerLoader) {
            $loader->setContext($context);
        }

        return $this->builder;
    }

    public function compile(string $path, ContextInterface $context): ViewInterface
    {
        // for name generation only
        $view = $this->getLoader()->load($path);

        // expected template class name
        $class = $this->className($view, $context);

        // cache key
        $key = $this->cacheKey($view, $context);

        if ($this->cache !== null && $this->cache->isFresh($key)) {
            $this->cache->load($key);
        } elseif (!\class_exists($class)) {
            try {
                $builder = $this->getBuilder($context);

                $result = $builder->compile($path);
            } catch (Throwable $e) {
                throw new CompileException($e);
            }

            $compiled = $this->compileClass($class, $result);

            if ($this->cache !== null) {
                $this->cache->write(
                    $key,
                    $compiled,
                    \array_map(
                        fn ($path) => $this->getLoader()->load($path)->getFilename(),
                        $result->getPaths()
                    )
                );

                $this->cache->load($key);
            }

            if (!\class_exists($class)) {
                // runtime initialization
                eval('?>' . $compiled);
            }
        }

        if (!\class_exists($class) || !\is_subclass_of($class, ViewInterface::class)) {
            throw new EngineException(\sprintf('Unable to load `%s`, cache might be corrupted.', $path));
        }

        return new $class($this, $view, $context);
    }

    public function reset(string $path, ContextInterface $context): void
    {
        if ($this->cache === null) {
            return;
        }

        $source = $this->getLoader()->load($path);

        $this->cache->delete($this->cacheKey($source, $context));
    }

    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return $this->compile($path, $context);
    }

    /**
     * Calculate sourcemap for exception highlighting.
     */
    public function makeSourceMap(string $path, ContextInterface $context): ?SourceMap
    {
        try {
            $builder = $this->getBuilder($context);

            // there is no need to cache sourcemaps since they are used during the exception only
            return $builder->compile($path)->getSourceMap($builder->getLoader());
        } catch (Throwable) {
            return null;
        }
    }

    private function compileClass(string $class, Result $result): string
    {
        $template = '<?php class %s extends \Spiral\Stempler\StemplerView {
            public function render(array $data=[]): string {
                \ob_start();
                $__outputLevel__ = \ob_get_level();

                try {
                    Spiral\Core\ContainerScope::runScope($this->container, function () use ($data) {
                        \extract($data, EXTR_OVERWRITE);
                        ?>%s<?php
                    });
                } catch (\Throwable $e) {
                    while (\ob_get_level() >= $__outputLevel__) { \ob_end_clean(); }
                    throw $this->mapException(8, $e, $data);
                } finally {
                    while (\ob_get_level() > $__outputLevel__) { \ob_end_clean(); }
                }

                return \ob_get_clean();
            }
        }';

        return \sprintf($template, $class, $result->getContent());
    }

    /**
     * @return class-string<ViewInterface>
     */
    private function className(ViewSource $source, ContextInterface $context): string
    {
        return $this->classPrefix . $this->cacheKey($source, $context);
    }

    private function cacheKey(ViewSource $source, ContextInterface $context): string
    {
        $key = \sprintf(
            '%s.%s.%s',
            $source->getNamespace(),
            $source->getName(),
            $context->getID()
        );

        return \hash('sha256', $key);
    }

    private function makeBuilder(StemplerLoader $loader): Builder
    {
        $builder = new Builder($loader);

        $directivesGroup = new DirectiveGroup();
        foreach ($this->getDirectives() as $directive) {
            $directivesGroup->addDirective($directive);
        }

        // we are using fixed set of grammars and renderers for now
        $builder->getParser()->addSyntax(
            new Grammar\PHPGrammar(),
            new Syntax\PHPSyntax()
        );

        $builder->getParser()->addSyntax(
            new Grammar\InlineGrammar(),
            new Syntax\InlineSyntax()
        );

        $builder->getParser()->addSyntax(
            new Grammar\DynamicGrammar($directivesGroup),
            new Syntax\DynamicSyntax()
        );

        $builder->getParser()->addSyntax(
            new Grammar\HTMLGrammar(),
            new Syntax\HTMLSyntax()
        );

        $builder->getCompiler()->addRenderer(new CoreRenderer());
        $builder->getCompiler()->addRenderer(new PHPRenderer());
        $builder->getCompiler()->addRenderer(new HTMLRenderer());
        $builder->getCompiler()->addRenderer(new DynamicRenderer(new DirectiveGroup($this->getDirectives())));

        // ATS modifications
        foreach ($this->getVisitors(Builder::STAGE_PREPARE) as $visitor) {
            $builder->addVisitor($visitor, Builder::STAGE_PREPARE);
        }

        // php conversion
        $builder->addVisitor(
            new DynamicToPHP(DynamicToPHP::DEFAULT_FILTER, $this->getDirectives()),
            Builder::STAGE_TRANSFORM
        );

        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);
        $builder->addVisitor(new ExtendsParent($builder), Builder::STAGE_TRANSFORM);

        foreach ($this->getVisitors(Builder::STAGE_TRANSFORM) as $visitor) {
            $builder->addVisitor($visitor, Builder::STAGE_TRANSFORM);
        }

        foreach ($this->getVisitors(Builder::STAGE_FINALIZE) as $visitor) {
            $builder->addVisitor($visitor, Builder::STAGE_FINALIZE);
        }

        foreach ($this->getVisitors(Builder::STAGE_COMPILE) as $visitor) {
            $builder->addVisitor($visitor, Builder::STAGE_COMPILE);
        }

        return $builder;
    }

    /**
     * @return VisitorInterface[]
     */
    private function getVisitors(int $stage): iterable
    {
        $result = [];
        foreach ($this->config->getVisitors($stage) as $visitor) {
            if ($visitor instanceof Autowire) {
                $result[] = $visitor->resolve($this->container->get(FactoryInterface::class));
                continue;
            }

            $result[] = $visitor;
        }

        return $result;
    }

    /**
     * @return ProcessorInterface[]
     */
    private function getProcessors(): iterable
    {
        $result = [];
        foreach ($this->config->getProcessors() as $processor) {
            if ($processor instanceof Autowire) {
                $result[] = $processor->resolve($this->container->get(FactoryInterface::class));
                continue;
            }

            $result[] = $processor;
        }

        return $result;
    }

    /**
     * @return DirectiveRendererInterface[]
     */
    private function getDirectives(): iterable
    {
        $result = [];
        foreach ($this->config->getDirectives() as $directive) {
            if ($directive instanceof Autowire) {
                $result[] = $directive->resolve($this->container->get(FactoryInterface::class));
                continue;
            }

            $result[] = $directive;
        }

        return $result;
    }
}
