<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines;

use Spiral\Core\Container\Autowire;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Stempler\Supervisor;
use Spiral\Stempler\Syntaxes\DarkSyntax;
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Prototypes\AbstractEngine;
use Spiral\Views\Engines\Stempler\LoaderBridge;
use Spiral\Views\Engines\Stempler\StemplerCache;
use Spiral\Views\Engines\Stempler\StemplerView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;

/**
 * Spiral Stempler template composer.
 */
class StemplerEngine extends AbstractEngine
{
    use BenchmarkTrait;

    /**
     * Container is needed to provide proper scope isolation at moment of rendering.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StemplerCache
     */
    protected $cache;

    /**
     * To be applied to view source before it's being loaded.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * To be applied to already compiled view source.
     *
     * @var array
     */
    protected $processors = [];

    /**
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     * @param FilesInterface       $files
     * @param ContainerInterface   $container
     * @param array                $modifiers
     * @param array                $processors
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        FilesInterface $files,
        ContainerInterface $container,
        array $modifiers = [],
        array $processors = []
    ) {
        parent::__construct($environment, $loader);

        $this->container = $container;

        $this->modifiers = $modifiers;
        $this->processors = $processors;

        $this->cache = new StemplerCache($this->environment, $files);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): ViewInterface
    {
        return new StemplerView(
            $this->compile($path),
            $this->container
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return ViewSource Points to compiled view source.
     */
    public function compile(string $path, bool $reset = false): ViewSource
    {
        $context = $this->loader->getSource($path);
        $cached = new ViewSource(
            $this->cache->cacheFilename($path),
            $context->getName(),
            $context->getNamespace()
        );

        if (
            $context->isFresh($this->cache->timeCached($cached->getFilename()))
            && !$reset
        ) {
            //Compiled and cached and not changed (simple cache check)
            return $cached;
        }

        $benchmark = $this->benchmark('compile', $path);
        try {
            //Compiling into cache
            $cached = $cached->withCode(
                $this->createSupervisor()->createNode($path)->compile()
            );
        } finally {
            $this->benchmark($benchmark);
        }

        $benchmark = $this->benchmark('cache', $path);
        try {
            //To simplify processors life let's write file to cache first (this might help in debugging)
            $this->cache->write(
                $cached->getFilename(),
                $cached->getCode()
            );

            //Ok, now we can apply processors
            $cached = $this->postProcess($cached);

            $this->cache->write(
                $cached->getFilename(),
                $cached->getCode()
            );
        } finally {
            $this->benchmark($benchmark);
        }

        return $cached;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function withEnvironment(EnvironmentInterface $environment): EngineInterface
    {
        /**
         * @var self $engine
         */
        $engine = parent::withEnvironment($environment);
        $engine->cache = $engine->cache->withEnvironment($environment);

        return $engine;
    }

    /**
     * Create new instance of supervisor.
     *
     * @return Supervisor
     */
    protected function createSupervisor(): Supervisor
    {
        //Prepare loader
        return new Supervisor($this->wrapLoader(), new DarkSyntax());
    }

    /**
     * Run associated processors for post-processing.
     *
     * @param \Spiral\Views\ViewSource $source
     *
     * @return ViewSource
     */
    protected function postProcess(ViewSource $source): ViewSource
    {
        foreach ($this->processors as $processor) {
            /**
             * @var ProcessorInterface
             */
            if (!is_object($processor) || $processor instanceof Autowire) {
                $processor = $this->container->get($processor);
            }

            $benchmark = $this->benchmark(
                'process',
                get_class($processor) . '-{' . $source->getName()
            );

            $source->getCode();

            try {
                //Post processing
                $source = $source->withCode(
                    $processor->modify($this->environment, $source, $source->getCode())
                );
            } finally {
                $this->benchmark($benchmark);
            }
        }

        return $source;
    }

    /**
     * In most of cases stempler will get view processors to be executed before composing, to run
     * such processors we need special wrapper at top of environment.
     *
     * @return \Spiral\Stempler\LoaderInterface
     */
    private function wrapLoader(): \Spiral\Stempler\LoaderInterface
    {
        $processors = [];
        foreach ($this->modifiers as $modifier) {
            if (!is_object($modifier) || $modifier instanceof Autowire) {
                $processors[] = $this->container->make($modifier);
            } else {
                $processors[] = $modifier;
            }
        }

        return new LoaderBridge($this->environment, $this->loader, $processors);
    }
}