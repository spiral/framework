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
     * Create view engine with new loader. MUST no alter original engine settings.
     *
     * @param LoaderInterface $loader
     *
     * @return EngineInterface
     */
    public function withLoader(LoaderInterface $loader): EngineInterface;

    /**
     * Create engine version with new view environment (should change cache behaviour). MUST no
     * alter original engine settings.
     *
     * @param EnvironmentInterface $environment
     *
     * @return EngineInterface
     */
    public function withEnvironment(EnvironmentInterface $environment): EngineInterface;

    /**
     * Pre-compile specified template/view.
     *
     * @param string $path
     * @param bool   $reset Ignore cache.
     *
     * @throws \Spiral\Views\Exceptions\CompileException
     */
    public function compile(string $path, bool $reset = false);
}