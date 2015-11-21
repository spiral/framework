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
use Spiral\Views\EngineInterface;
use Spiral\Views\Engines\Native\NativeView;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;

/**
 * The simpliest view engine, simply renders php files.
 */
class NativeEngine extends Component implements EngineInterface
{
    /**
     * Saturation of files.
     */
    use SaturateTrait;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param LoaderInterface      $loader
     * @param EnvironmentInterface $environment
     * @param ContainerInterface   $files
     */
    public function __construct(
        LoaderInterface $loader,
        EnvironmentInterface $environment,
        ContainerInterface $files = null
    ) {
        $this->container = $this->saturate($files, ContainerInterface::class);

        $this->setEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        return new NativeView(
            $this->loader->localFilename($path),
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
     */
    public function compile($path)
    {
        //Can not be compiled
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
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
        //Does not do anything
    }
}