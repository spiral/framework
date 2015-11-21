<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines;

use Spiral\Core\Component;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Twig\TwigCache;
use Spiral\Views\Engines\Twig\TwigView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;

/**
 * Wraps and control twig engine.
 */
class TwigEngine extends Component implements EngineInterface
{
    /**
     * Saturation of files.
     */
    use SaturateTrait;

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
     * @param array                $options
     */
    public function __construct(
        LoaderInterface $loader,
        EnvironmentInterface $environment,
        FilesInterface $files = null,
        array $options = []
    ) {
        $this->twig = new \Twig_Environment($loader, $options);
        $this->files = $this->saturate($files, FilesInterface::class);

        $this->setLoader($loader)->setEnvironment($environment);
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
     */
    public function get($path)
    {
        return $this->twig->loadTemplate($path);
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
    public function compile($path)
    {
        $this->twig->loadTemplate($path);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setLoader(LoaderInterface $loader)
    {
        $this->twig->setLoader($loader);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
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