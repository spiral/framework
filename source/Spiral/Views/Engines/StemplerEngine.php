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
 * Spiral Stempler template composer.
 */
abstract class StemplerEngine extends AbstractEngine
{
    /**
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
        return new NativeView(
            $this->compile($path),
            $this->loader->fetchNamespace($path),
            $this->loader->fetchName($path),
            $this->container
        );
    }
}