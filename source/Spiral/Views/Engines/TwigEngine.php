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
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Traits\ModifiersTrait;
use Spiral\Views\Engines\Twig\Exceptions\SyntaxException;
use Spiral\Views\Engines\Twig\TwigCache;
use Spiral\Views\Engines\Twig\TwigView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Loaders\ModifiableLoader;

/**
 * Wraps and control twig engine.
 */
class TwigEngine extends Component implements EngineInterface
{
    /**
     * Saturation of files.
     */
    use SaturateTrait, BenchmarkTrait, ModifiersTrait;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var \Twig_Environment
     */
    protected $twig = null;

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
     * @param array                $options
     */
    public function __construct(
        LoaderInterface $loader,
        EnvironmentInterface $environment,
        FilesInterface $files = null,
        ContainerInterface $container = null,
        array $modifiers = [],
        array $options = []
    ) {
        $this->twig = new \Twig_Environment($loader, $options);
        $this->container = $this->saturate($container, ContainerInterface::class);
        $this->files = $this->saturate($files, FilesInterface::class);

        $this->setEnvironment($environment);
        $this->modifiers = $modifiers;
        $this->setLoader($loader);

        $this->configure($this->twig);
    }

    /**
     * Get associated twig environment.
     *
     * @return \Twig_Environment
     */
    public function twig()
    {
        return $this->twig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxException
     */
    public function get($path)
    {
        $benchmark = $this->benchmark('load', $path);
        try {
            return $this->twig->loadTemplate($path);
        } catch (\Twig_Error_Syntax $exception) {
            //Let's clarify exception location
            throw SyntaxException::fromTwig($exception, $this->loader);
        } finally {
            $this->benchmark($benchmark);
        }
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
     */
    public function compile($path, $reset = false)
    {
        if ($reset) {
            $cache = $this->twig->getCache();
            $this->twig->setCache(false);
        }

        $this->get($path);

        if ($reset && !empty($cache)) {
            //Restoring cache
            $this->twig->setCache($cache);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setLoader(LoaderInterface $loader)
    {
        if (!empty($this->modifiers)) {
            //Let's prepare source before giving it to Stempler
            $loader = new ModifiableLoader($loader, $this->getModifiers());
        }

        $this->twig->setLoader($this->loader = $loader);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
        if (!$environment->cachable()) {
            $this->twig->setCache(false);

            return $this;
        }

        $this->twig->setCache(new TwigCache($this->files, $environment));

        return $this;
    }

    /**
     * Configure twig environment.
     *
     * @param \Twig_Environment $environment
     * @return \Twig_Environment
     */
    protected function configure(\Twig_Environment $environment)
    {
        $environment->setBaseTemplateClass(TwigView::class);

        return $environment;
    }
}