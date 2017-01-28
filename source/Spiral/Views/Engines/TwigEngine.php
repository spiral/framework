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
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Prototypes\AbstractEngine;
use Spiral\Views\Engines\Twig\Exceptions\CompileException;
use Spiral\Views\Engines\Twig\LoaderBridge;
use Spiral\Views\Engines\Twig\TwigCache;
use Spiral\Views\Engines\Twig\TwigView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Loaders\ModifiableLoader;
use Spiral\Views\ViewInterface;

/**
 * Wraps and control twig engine.
 */
class TwigEngine extends AbstractEngine
{
    use BenchmarkTrait;

    /**
     * Set of class names (processors) to be applied to view sources befor giving it to Twig.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var FilesInterface
     */
    protected $files;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TwigEngine constructor.
     *
     * @param EnvironmentInterface    $environment
     * @param LoaderInterface         $loader
     * @param FilesInterface|null     $files
     * @param ContainerInterface|null $container
     * @param array                   $modifiers
     * @param array                   $extensions
     * @param array                   $options
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        FilesInterface $files = null,
        ContainerInterface $container = null,
        array $modifiers = [],
        array $extensions = [],
        array $options = []
    ) {
        parent::__construct($environment, $loader);
        $this->container = $container;
        $this->files = $files;
        $this->modifiers = $modifiers;

        //Initiating twig Environment
        $this->twig = $this->makeTwig($extensions, $options);
    }

    /**
     * Get associated twig environment.
     *
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws CompileException
     */
    public function get(string $path): ViewInterface
    {
        $benchmark = $this->benchmark('load', $path);

        try {
            return new TwigView($this->twig->load($path));
        } catch (\Twig_Error_Syntax $exception) {
            //Let's clarify exception location
            throw CompileException::fromTwig($exception);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $context = []): string
    {
        $benchmark = $this->benchmark('render', $path);
        try {
            return $this->get($path)->render($context);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $path, bool $reset = false)
    {
        if ($reset) {
            $cache = $this->twig->getCache();
            $this->twig->setCache(false);
        }

        //This must force twig to compile template
        $this->get($path);

        if ($reset && !empty($cache)) {
            //Restoring cache
            $this->twig->setCache($cache);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        /**
         * @var self $engine
         */
        $engine = parent::withLoader($loader);

        $engine->twig = clone $this->twig;
        $engine->twig->setLoader($engine->makeLoader());

        return $engine;
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

        $engine->twig = clone $this->twig;
        $engine->twig->setLoader($engine->makeLoader());
        $engine->twig->setCache($engine->makeCache());

        return $engine;
    }

    /**
     * Initiating twig environment.
     *
     * @param array $extensions
     * @param array $options
     *
     * @return \Twig_Environment
     */
    protected function makeTwig(array $extensions, array $options): \Twig_Environment
    {
        //Initiating Twig Environment
        $twig = $this->container->make(\Twig_Environment::class, [
            'loader'  => $this->makeLoader(),
            'options' => $options
        ]);

        $twig->setCache($this->makeCache());

        foreach ($extensions as $extension) {
            //Each extension can be delivered thought container
            $twig->addExtension($this->container->get($extension));
        }

        return $twig;
    }

    /**
     * In most of cases twig will get view processors to be executed before twig itself, to run
     * such processors we need special wrapper at top of environment.
     *
     * @return \Twig_LoaderInterface
     */
    private function makeLoader(): \Twig_LoaderInterface
    {
        $modifiers = [];
        foreach ($this->modifiers as $modifier) {
            if (is_object($modifier)) {
                $modifiers[] = $modifier;
            } else {
                $modifiers[] = $this->container->make($modifier);
            }
        }

        return new LoaderBridge(
            new ModifiableLoader($this->environment, $this->loader, $modifiers)
        );
    }

    /**
     * @return bool|TwigCache
     */
    private function makeCache()
    {
        if (!$this->environment->isCachable()) {
            return false;
        }

        return new TwigCache($this->files, $this->environment);
    }
}