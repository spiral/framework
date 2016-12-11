<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Views\Engines;

use Spiral\Core\ContainerInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Stempler\Supervisor;
use Spiral\Stempler\Syntaxes\DarkSyntax;
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Native\NativeView;
use Spiral\Views\Engines\Prototypes\AbstractEngine;
use Spiral\Views\Engines\Stempler\StemplerCache;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ProcessableLoader;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewInterface;

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

        $this->cache = new StemplerCache($files, $this->environment);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): ViewInterface
    {
        return new NativeView(
            $this->compile($path),
            $this->loader->fetchNamespace($path),
            $this->loader->fetchName($path),
            $this->container
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return string Cached filename.
     */
    public function compile(string $path, bool $reset = false): string
    {
        $cached = $this->cache->generateKey($path);

        if ($this->loader->isFresh($path, $this->cache->getTimestamp($cached)) && !$reset) {
            //Compiled and cached
            return $cached;
        }

        $benchmark = $this->benchmark('compile', $path);
        try {
            $source = $this->createSupervisor()->createNode($path)->compile();
        } finally {
            $this->benchmark($benchmark);
        }

        $benchmark = $this->benchmark('cache', $path);
        try {
            //To simplify processors life let's write file to cache first (this might help in debugging)
            $this->cache->write($cached, $source);

            //Ok, now we can apply processors
            $this->cache->write($cached, $this->processSource($source, $path));
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
        return new Supervisor($this->defineLoader(), new DarkSyntax());
    }

    /**
     * Process compiled source using Stempler post-processors.
     *
     * @param string $source
     * @param string $path
     *
     * @return string
     */
    protected function processSource(string $source, string $path): string
    {
        foreach ($this->processors as $processor) {
            /**
             * @var ProcessorInterface
             */
            if (!is_object($processor)) {
                $processor = $this->container->make($processor);
            }

            $benchmark = $this->benchmark('process', get_class($processor) . '-{' . $path);
            try {
                //Post processing
                $source = $processor->process(
                    $this->environment,
                    $source,
                    $this->loader->fetchNamespace($path),
                    $this->loader->fetchName($path)
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
     * @return LoaderInterface
     */
    private function defineLoader(): LoaderInterface
    {
        $processors = [];
        foreach ($this->modifiers as $modifier) {
            if (is_object($modifier)) {
                $processors[] = $modifier;
            } else {
                $processors[] = $this->container->make($modifier);
            }
        }

        return new ProcessableLoader($this->environment, $this->loader, $processors);
    }
}