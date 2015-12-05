<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Stempler\Supervisor;
use Spiral\Stempler\Syntaxes\DarkSyntax;
use Spiral\Stempler\SyntaxInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Stempler\StemplerCache;
use Spiral\Views\Engines\Stempler\StemplerView;
use Spiral\Views\Engines\Traits\ModifiersTrait;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Loaders\ModifiableLoader;
use Spiral\Views\ProcessorInterface;

/**
 * Spiral Stempler templater.
 */
class StemplerEngine extends Component implements EngineInterface
{
    /**
     * Saturation of files.
     */
    use SaturateTrait, BenchmarkTrait, ModifiersTrait;

    /**
     * @var StemplerCache
     */
    protected $cache = null;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var SyntaxInterface
     */
    protected $syntax = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param LoaderInterface      $loader
     * @param EnvironmentInterface $environment
     * @param FilesInterface       $files
     * @param ContainerInterface   $container
     * @param array                $modifiers
     * @param array                $processors
     * @param array                $options
     */
    public function __construct(
        LoaderInterface $loader,
        EnvironmentInterface $environment,
        FilesInterface $files = null,
        ContainerInterface $container = null,
        array $modifiers = [],
        array $processors = [],
        array $options = []
    ) {
        //Needed components
        $this->container = $this->saturate($container, ContainerInterface::class);
        $this->files = $this->saturate($files, FilesInterface::class);

        $this->syntax = new DarkSyntax(!empty($options['strict']));

        $this->setEnvironment($environment);
        $this->modifiers = $modifiers;
        $this->processors = $processors;
        $this->setLoader($loader);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        return new StemplerView(
            $this->compile($path),
            $this->loader->viewNamespace($path),
            $this->loader->viewName($path),
            $this->container
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render($path, array $context = [])
    {
        return $this->get($path)->render($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return string Cached filename.
     */
    public function compile($path, $reset = false)
    {
        $cached = $this->cache->generateKey($path);

        if ($this->loader->isFresh($path, $this->cache->getTimestamp($cached)) && !$reset) {
            //Compiled and cached
            return $cached;
        }

        //Compiling!
        $benchmark = $this->benchmark('compile', $path);
        try {
            $source = $this->supervisor()->createNode($path)->compile();
        } finally {
            $this->benchmark($benchmark);
        }

        $benchmark = $this->benchmark('cache', $path);
        try {
            //To simplify processors life let's write file to cache first (this might help in debugging)
            $this->cache->write($cached, $source);

            //Ok, now we can apply processors
            $this->cache->write($cached, $this->processSource($source, $path, $cached));
        } finally {
            $this->benchmark($benchmark);
        }

        return $cached;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoader(LoaderInterface $loader)
    {
        if (!empty($this->modifiers)) {
            //Let's prepare source before giving it to Stempler
            $loader = new ModifiableLoader($loader, $this->getModifiers());
        }

        $this->loader = $loader;
        $this->cache = new StemplerCache($this->files, $this->environment, $this->loader);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        if (!empty($this->loader)) {
            $this->cache = new StemplerCache($this->files, $environment, $this->loader);
        }

        return $this;
    }

    /**
     * Create new instance of supervisor.
     *
     * @return Supervisor
     */
    protected function supervisor()
    {
        //Prepare loader
        return new Supervisor($this->loader, $this->syntax);
    }

    /**
     * Process compiled source using Stempler post-processors.
     *
     * @param string $source
     * @param string $path
     * @param string $compiledFilename
     * @return string
     */
    protected function processSource($source, $path, $compiledFilename = null)
    {
        foreach ($this->getProcessors() as $processor) {
            $benchmark = $this->benchmark('process', $path . '@' . get_class($processor));
            try {
                $source = $processor->process(
                    $source,
                    $this->loader->viewNamespace($path),
                    $this->loader->viewName($path),
                    $compiledFilename
                );
            } finally {
                $this->benchmark($benchmark);
            }
        }

        return $source;
    }

    /**
     * Initiate set of modifiers.
     *
     * @return ProcessorInterface[]
     */
    protected function getProcessors()
    {
        foreach ($this->processors as $index => $processor) {
            if (!is_object($processor)) {
                //Initiating using container
                $this->processors[$index] = $this->container->get($processor);
            }
        }

        return $this->processors;
    }
}