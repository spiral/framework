<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Default spiral implementation of view class. You can link your custom view implementations via
 * editing view config section - associations. You can use $this->container inside view source.
 */
class View extends Component implements ViewInterface
{
    /**
     * For render benchmarking.
     */
    use BenchmarkTrait;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var CompilerInterface
     */
    protected $compiler = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ContainerInterface $container,
        CompilerInterface $compiler,
        array $data = []
    ) {
        $this->container = $container;
        $this->compiler = $compiler;
        $this->data = $data;
    }

    /**
     * Alter view parameters (should replace existed value).
     *
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Set view rendering data. Full dataset will be replaced.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $__benchmark__ = $this->benchmark(
            'render',
            $this->compiler->getNamespace()
            . ViewProviderInterface::NS_SEPARATOR
            . $this->compiler->getView()
        );

        $__outputLevel__ = ob_get_level();
        ob_start();

        extract($this->data, EXTR_OVERWRITE);
        try {
            require $this->compiler->compiledFilename();
        } finally {
            while (ob_get_level() > $__outputLevel__ + 1) {
                ob_end_clean();
            }

            $this->benchmark($__benchmark__);
        }

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return $this->render();
    }
}