<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Native;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Traits\SharedTrait;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Views\Exceptions\RenderException;
use Spiral\Views\ViewInterface;

/**
 * Simpliest implement of view model used by native and Stempler engines. Provides ability to
 * perform calls like $this->app in a view file.
 *
 * Attention, this view model depends on container in order to provider proper scope/isolation
 * when view is being rendered.
 */
class NativeView extends Component implements ViewInterface
{
    use BenchmarkTrait, SharedTrait;

    /**
     * View filename.
     *
     * @var string
     */
    protected $filename = null;

    /**
     * View namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * View name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param string             $filename
     * @param string             $namespace
     * @param string             $name
     * @param ContainerInterface $container
     */
    public function __construct(
        string $filename,
        string $namespace,
        string $name,
        ContainerInterface $container
    ) {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->name = $name;

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $context = []): string
    {
        $__benchmark__ = $this->benchmark('render', "{$this->namespace}:{$this->name}");

        ob_start();
        $__outputLevel__ = ob_get_level();

        $scope = self::staticContainer($this->container);
        try {
            extract($context, EXTR_OVERWRITE);
            require $this->filename;
        } catch (\Throwable $e) {
            //Wrapping exception
            throw new RenderException($e->getMessage(), $e->getCode(), $e);
        } finally {
            while (ob_get_level() > $__outputLevel__) {
                ob_end_clean();
            }

            $this->benchmark($__benchmark__);
            self::staticContainer($scope);
        }

        return ob_get_clean();
    }
}