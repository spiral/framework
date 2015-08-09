<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Models\DataEntity;

/**
 * Default spiral implementation of view class. You can link your custom view implementations via
 * editing view config section - associations.
 */
class View extends DataEntity implements ViewInterface
{
    /**
     * For render benchmarking.
     */
    use BenchmarkTrait;

    /**
     * @invisible
     * @var CompilerInterface
     */
    protected $compiler = null;

    /**
     * @param CompilerInterface $compiler
     * @param array             $data Contract with viewManager.
     */
    public function __construct(CompilerInterface $compiler, array $data = [])
    {
        $this->compiler = $compiler;
        $this->setFields($data);

        //Traits
        self::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        //Benchmarking context
        $__context__ = $this->compiler->getNamespace()
            . ViewProviderInterface::NS_SEPARATOR
            . $this->compiler->getView();

        $this->benchmark('render', $__context__);

        $__outputLevel__ = ob_get_level();
        ob_start();

        extract($this->fields, EXTR_OVERWRITE);
        try {
            require $this->compiler->compiledFilename();
        } finally {
            while (ob_get_level() > $__outputLevel__ + 1) {
                ob_end_clean();
            }
        }

        $result = ob_get_clean();

        $this->benchmark('render', $__context__);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return $this->render();
    }
}