<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

/**
 * All view engines must implement such interface.
 */
interface EngineInterface extends ViewsInterface
{
    /**
     * @param LoaderInterface      $loader
     * @param EnvironmentInterface $environment
     */
    public function __construct(LoaderInterface $loader, EnvironmentInterface $environment);

    /**
     * Change engine view loader.
     *
     * @param LoaderInterface $loader
     */
    public function setLoader(LoaderInterface $loader);

    /**
     * Change view environment (should change cache behaviour).
     *
     * @param EnvironmentInterface $environment
     */
    public function setEnvironment(EnvironmentInterface $environment);

    /**
     * Pre-compile specified template/view.
     *
     * @param string $path
     */
    public function compile($path);
}