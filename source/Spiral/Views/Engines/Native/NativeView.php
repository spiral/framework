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
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Views\ViewInterface;

class NativeView extends Component implements ViewInterface
{
    /**
     * Container saturation.
     */
    use SaturateTrait, BenchmarkTrait;

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
     * @param string                  $filename
     * @param string                  $namespace
     * @param string                  $name
     * @param ContainerInterface|null $container
     */
    public function __construct($filename, $namespace, $name, ContainerInterface $container = null)
    {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->name = $name;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $context = [])
    {
        $__benchmark__ = $this->benchmark('render', "{$this->namespace}:{$this->name}");

        ob_start();
        $__outputLevel__ = ob_get_level();

        try {
            extract($context, EXTR_OVERWRITE);
            require $this->filename;
        } finally {
            while (ob_get_level() > $__outputLevel__) {
                ob_end_clean();
            }

            $this->benchmark($__benchmark__);
        }

        return ob_get_clean();
    }
}