<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FileManager;
use Spiral\Files\FilesInterface;
use Spiral\Views\Configs\ViewsConfig;
use Spiral\Views\Exceptions\LoaderException;
use Spiral\Views\Exceptions\ViewsException;

class ViewManager extends Component implements ViewsInterface, SingletonInterface
{
    use BenchmarkTrait;

    /**
     * Active view environment might define behaviour of engines and etc.
     *
     * @var EnvironmentInterface
     */
    private $environment = null;

    /**
     * Loader used to locate view files using simple notation (where no extension is included).
     *
     * @var LoaderInterface
     */
    private $loader = null;

    /**
     * View engines cache.
     *
     * @var EngineInterface[]
     */
    private $engines = [];

    /**
     * @var ViewsConfig
     */
    protected $config = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ViewsConfig        $config
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     */
    public function __construct(
        ViewsConfig $config,
        FilesInterface $files = null,
        ContainerInterface $container = null
    ) {
        $this->config = $config;
        $this->files = $files ?? new FileManager();
        $this->container = $container ?? new Container();

        //Define engine's behaviour
        $this->loader = new ViewLoader($config->getNamespaces(), $files, $container);
        $this->environment = $this->createEnvironment($config);
    }

    /**
     * Creates copy of view manager with new environment.
     *
     * @param EnvironmentInterface $environment
     *
     * @return ViewManager
     */
    public function withEnvironment(EnvironmentInterface $environment): ViewManager
    {
        $views = clone $this;
        $views->loader = clone $this->loader;
        $views->environment = $environment;

        //Not carrying already built engines with us
        $views->engines = [];

        return $views;
    }

    /**
     * Current view environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    /**
     * @param LoaderInterface $loader
     *
     * @return ViewManager
     */
    public function withLoader(LoaderInterface $loader): ViewManager
    {
        $views = clone $this;
        $views->loader = $loader;
        $views->environment = clone $this->environment;

        //Not carrying already built engines with us
        $views->engines = [];

        return $views;
    }

    /**
     * View loader.
     *
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): ViewInterface
    {
        $engine = $this->detectEngine($path);

        return $this->engine($engine)->get($path);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $context = []): string
    {
        $engine = $this->detectEngine($path);

        return $this->engine($engine)->render($path, $context);
    }

    /**
     * Pre-compile desired view file.
     *
     * @param string $path
     */
    public function compile(string $path)
    {
        $engine = $this->detectEngine($path);

        $this->engine($engine)->compile($path);
    }

    /**
     * Get engine by it's type.
     *
     * @param string $engine
     *
     * @return EngineInterface
     */
    public function engine(string $engine): EngineInterface
    {
        //Checking for an instance in cache
        if (!isset($this->engines[$engine])) {
            $this->engines[$engine] = $this->createEngine($engine);
        }

        return $this->engines[$engine];
    }

    /**
     * Detect engine by view path (automatically resolved based on extension).
     *
     * @todo this method might require some optimizations, for example utilizing memory cache for
     * @todo associations will be a nice idea.
     *
     * @param string $path
     *
     * @return string
     */
    protected function detectEngine(string $path): string
    {
        //File extension can help us to detect engine faster (attention, does not work with complex
        //extensions at this moment).
        $extension = $this->files->extension($path);

        $result = null;
        foreach ($this->config->getEngines() as $engine) {
            if (!empty($extension) && $extension == $this->config->engineExtension($engine)) {
                //Found by extension
                $result = $engine;

                break;
            }

            //Trying automatic (no extension) detection
            $loader = $this->isolatedLoader($engine);

            try {
                if (!empty($loader->fetchName($path))) {
                    $result = $engine;
                }
            } catch (LoaderException $exception) {
                //Does not related to such engine
            }
        }

        if (empty($result)) {
            throw new ViewsException("Unable to detect view engine for '{$path}'");
        }

        return $result;
    }

    /**
     * Create engine instance.
     *
     * @param string $engine
     *
     * @return EngineInterface
     *
     * @throws ViewsException
     */
    protected function createEngine(string $engine): EngineInterface
    {
        if (!$this->config->hasEngine($engine)) {
            throw new ViewsException("Undefined engine '{$engine}'");
        }

        //Populating constructor parameters
        $options = $this->config->engineOptions($engine);
        $options += [
            'loader'      => $this->isolatedLoader($engine),
            'environment' => $this->getEnvironment()
        ];

        //We have to create an engine
        $benchmark = $this->benchmark('engine', $engine);
        try {
            //Creating engine instance
            return $this->container->make($this->config->engineClass($engine), $options);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * @param ViewsConfig $config
     *
     * @return EnvironmentInterface
     */
    protected function createEnvironment(ViewsConfig $config): EnvironmentInterface
    {
        return new DynamicEnvironment(
            $config->environmentDependencies(),
            $config->cacheEnabled(),
            $config->cacheDirectory(),
            $this->container
        );
    }

    /**
     * Getting isolated view loader (class responsible for locating files and isolating view
     * namespaces). Isolation is done by forcing specific file extension. MUST NOT return same
     * instance for different engines!
     *
     * @param string $engine Forced extension value.
     *
     * @return LoaderInterface
     *
     * @throws ViewsException
     */
    protected function isolatedLoader(string $engine = null): LoaderInterface
    {
        $extension = null;
        if (!empty($engine)) {
            if (!$this->config->hasEngine($engine)) {
                throw new ViewsException("Undefined view engine '{$engine}'");
            }

            $extension = $this->config->engineExtension($engine);
        }

        return $this->loader->withExtension($extension);
    }
}