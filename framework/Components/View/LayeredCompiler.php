<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Components\View;

use Spiral\Core\Component;
use Spiral\Core\Container;

class LayeredCompiler extends Component implements CompilerInterface
{

    protected $filename = '';

    protected $view = '';
    protected $namespace = '';


    /**
     * View processors. Processors used to pre-process view source and save it to cache, in normal
     * operation mode processors will be called only once and never during user request.
     *
     * @var ProcessorInterface[]
     */
    protected $processors = array();

    public function __construct($view, $namespace, $source, array $processors)
    {
        $this->source = $source;
        $this->processors = $processors;
    }

    /**
     * Getting view processor by name, processor will be loaded and configured automatically. Processors
     * are created only for pre-processing view source to create static cache, this means you should't
     * expect too high performance and optimizations inside, due it's more important to have good
     * functionality and reliable results.
     *
     * You should never user view component in production with disabled cache, this will slow down
     * your website dramatically.
     *
     * @param string $name
     * @return ProcessorInterface
     */
    public function getProcessor($name)
    {
        if (isset($this->processors[$name]))
        {
            return $this->processors[$name];
        }

        $config = $this->processors[$name];

        return $this->processors[$name] = Container::get(
            $config['class'],
            array(
                'options'  => $config,
                'compiler' => $this
            )
        );
    }

    public function compile()
    {

        foreach (array_keys($this->processors) as $processor)
        {
            benchmark('view::' . $processor, $this->namespace . ':' . $this->view);

            //Compiling
            $source = $this->getProcessor($processor)->processSource(
                $this->source,
                $this->namespace,
                $this->view,

                $this->filename
            );

            benchmark('view::' . $processor, $this->namespace . ':' . $this->view);
        }

        return $source;
    }
}