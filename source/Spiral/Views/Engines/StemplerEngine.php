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
use Spiral\Stempler\Syntaxes\WooSyntax;
use Spiral\Stempler\SyntaxInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Stempler\StemplerCache;
use Spiral\Views\Engines\Stempler\StemplerView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Loaders\ModifiableLoader;
use Spiral\Views\ModifierInterface;
use Spiral\Views\ProcessorInterface;

/**
 * Spiral Stempler templater.
 */
class StemplerEngine extends Component implements EngineInterface
{
    /**
     * Saturation of files.
     */
    use SaturateTrait, BenchmarkTrait;

    /**
     * Modifier class names.
     *
     * @var array|ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * Processor class names.
     *
     * @var array|ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

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
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

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

        $this->syntax = new WooSyntax(!empty($options['strict']));

        $this->setLoader($loader)->setEnvironment($environment);

        $this->modifiers = $modifiers;
        $this->processors = $processors;
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
    public function compile($path)
    {
        $cached = $this->cache->generateKey($path);
        if ($this->loader->isFresh($path, $this->cache->getTimestamp($cached))) {
            //Compiled and cached
            return $cached;
        }

        //Compiling!
        $benchmark = $this->benchmark('compiling', $path);
        try {
            $source = $this->supervisor()->createNode($path)->compile();
        } finally {
            $this->benchmark($benchmark);
        }

        $benchmark = $this->benchmark('caching', $path);
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
        $this->loader = $loader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
        $this->cache = new StemplerCache($this->files, $environment);

        return $this;
    }

    /**
     * Create new instance of supervisor.
     *
     * @return Supervisor
     */
    protected function supervisor()
    {
        $loader = $this->loader;

        if (!empty($this->modifiers)) {
            //Let's prepare source before giving it to Stempler
            $loader = new ModifiableLoader($loader, $this->getModifiers());
        }

        //Prepare loader
        return new Supervisor($loader, $this->syntax);
    }

    /**
     * Initiate set of modifiers.
     *
     * @return ModifierInterface[]
     */
    protected function getModifiers()
    {
        foreach ($this->modifiers as $index => $modifier) {
            if (!is_object($modifier)) {
                //Initiating using container
                $this->modifiers[$index] = $this->container->construct($modifier, [
                    'environment' => $this->environment
                ]);
            }
        }

        return $this->modifiers;
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
                $source = $processor->process($source, $compiledFilename);
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