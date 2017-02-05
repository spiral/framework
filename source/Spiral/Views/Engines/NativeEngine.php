<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines;

use Spiral\Core\ContainerInterface;
use Spiral\Views\Engines\Native\NativeView;
use Spiral\Views\Engines\Prototypes\AbstractEngine;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewInterface;

/**
 * Default, php based view engine.
 */
class NativeEngine extends AbstractEngine
{
    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Container is needed to provide proper scope isolation at moment of rendering.
     *
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     * @param ContainerInterface   $container
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        ContainerInterface $container
    ) {
        parent::__construct($environment, $loader);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): ViewInterface
    {
        return new NativeView($this->loader->getSource($path), $this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $path, bool $reset = false)
    {
        //Can not be compiled
    }
}