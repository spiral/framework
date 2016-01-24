<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\Configs\ViewsConfig;
use Spiral\Views\Exceptions\LoaderException;
use Spiral\Views\Exceptions\ViewsException;

/**
 * Default ViewsInterface implementation with ability to change cache versions via external
 * dependencies. ViewManager support multiple namespaces and namespaces associated with multiple
 * folders.
 *
 * @todo improve view engine location method
 * @todo cache associations between view name and engine
 */
class ViewManager extends Component implements SingletonInterface, ViewsInterface
{
    use BenchmarkTrait;

    /**
     * Associations between view path and engine.
     *
     * @var array
     */
    private $associationCache = [];

    /**
     * Pre-constructed engines.
     *
     * @var EngineInterface[]
     */
    protected $engines = [];

    /**
     * @var ViewsConfig
     */
    protected $config = null;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

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
     * @todo Memory?
     * @param ViewsConfig          $config
     * @param FilesInterface       $files
     * @param ContainerInterface   $container
     */
    public function __construct(
        ViewsConfig $config,
        FilesInterface $files,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->files = $files;
        $this->container = $container;

        $this->loader = new ViewLoader($config->getNamespaces(), $files);
        $this->environment = new ViewEnvironment(
            $config->environmentDependencies(),
            $config->cacheEnabled(),
            $config->cacheDirectory(),
            $container
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        return $this->engine($this->detectEngine($path))->get($path);
    }

    /**
     * {@inheritdoc}
     */
    public function render($path, array $context = [])
    {
        return $this->engine($this->detectEngine($path))->render($path, $context);
    }

    /**
     * Pre-compile desired view file.
     *
     * @param string $path
     */
    public function compile($path)
    {
        $this->engine($this->detectEngine($path))->compile($path);
    }

    /**
     * Get engine by it's type.
     *
     * @param string $engine
     * @param bool   $reload If true engine will receive new instance of loader and enviroment.
     * @return EngineInterface
     */
    public function engine($engine, $reload = false)
    {
        if (!isset($this->engines[$engine])) {
            $parameters = $this->config->engineParameters($engine);
            $parameters['loader'] = $this->loader($engine);
            $parameters['environment'] = $this->environment();

            //We have to create an engine
            $this->engines[$engine] = $this->container->make(
                $this->config->engineClass($engine),
                $parameters
            );

            $reload = true;
        }

        //Configuring engine
        if ($reload) {
            $this->engines[$engine]->setLoader($this->loader($engine));
            $this->engines[$engine]->setEnvironment($this->environment());
        }

        return $this->engines[$engine];
    }

    /**
     * Get view loader.
     *
     * @param string $engine Forced extension value.
     * @return LoaderInterface
     */
    public function loader($engine = null)
    {
        $extension = null;
        if (!empty($engine)) {
            if (!$this->config->hasEngine($engine)) {
                throw new ViewsException("Undefined view engine '{$engine}'.");
            }

            $extension = $this->config->engineExtension($engine);
        }

        if (empty($extension)) {
            return $this->loader;
        }

        //todo: think about it
        return $this->loader->withExtension($extension);
    }

    /**
     * Current view environment changes cached filenames.
     *
     * @return EnvironmentInterface
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * Detect compiler by view path (automatically resolved based on extension).
     *
     * @todo cache needed?
     * @param string $path
     * @return string
     */
    protected function detectEngine($path)
    {
        if (isset($this->associationCache[$path])) {
            return $this->associationCache[$path];
        }

        //File extension can help us to detect engine faster (attention, does not work with complex
        //extensions at this moment).
        $extension = $this->files->extension($path);
        $detected = null;

        foreach ($this->config->getEngines() as $engine) {
            if (!empty($extension) && $extension == $this->config->engineExtension($engine)) {
                //Found by extension
                $detected = $engine;
                break;
            }

            //Trying automatic (no extension) detection
            $loader = $this->loader($engine);

            try {
                if (!empty($loader->viewName($path))) {
                    $detected = $engine;
                }
            } catch (LoaderException $exception) {
                //Does not related to such engine
            }
        }

        if (empty($detected)) {
            throw new ViewsException("Unable to detect view engine for '{$path}'.");
        }

        return $this->associationCache[$path] = $detected;
    }
}