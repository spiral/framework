<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\CompilerInterface;
use Spiral\Components\View\ViewManager;

class Compiler implements CompilerInterface
{
    /**
     * ViewManager component.
     *
     * @invisible
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * Configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Non compiled view source.
     *
     * @var string
     */
    protected $source = '';

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
    protected $view = '';

    /**
     * View processors. Processors used to pre-process view source and save it to cache, in normal
     * operation mode processors will be called only once and never during user request.
     *
     * @var array|ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * Instance of view compiler. Compilers used to pre-process view files for faster rendering in
     * runtime environment.
     *
     * @param ViewManager $viewManager
     * @param array       $config    Compiler configuration.
     * @param string      $source    Non-compiled source.
     * @param string      $namespace View namespace.
     * @param string      $view      View name.
     */
    public function __construct(ViewManager $viewManager, array $config, $source, $namespace, $view)
    {
        $this->viewManager = $viewManager;
        $this->config = $config;

        $this->source = $source;

        $this->namespace = $namespace;
        $this->view = $view;
    }

    public function getCopy($namespace, $view)
    {
        $compiler = clone $this;

        $compiler->namespace = $namespace;
        $compiler->view = $view;

        $compiler->source = $this->viewManager->getSource($namespace, $view);
        $compiler->processors = [];

        return $compiler;
    }

    /**
     * Active namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Active view name.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get list of all view processors.
     *
     * @return ProcessorInterface[]
     */
    public function getProcessors()
    {
        if (!empty($this->processors))
        {
            return $this->processors;
        }

        foreach ($this->config['processors'] as $name => $processor)
        {
            $this->processors[$name] = $this->viewManager->getContainer()->get($processor['class'], [
                'viewManager' => $this->viewManager,
                'compiler'    => $this,
                'options'     => $processor
            ]);
        }

        return $this->processors;
    }

    /**
     * Compile original view file to plain php code.
     *
     * @return string
     */
    public function compile()
    {
        $source = $this->source;
        foreach ($this->getProcessors() as $name => $processor)
        {
            benchmark('view::' . $name, $this->namespace . ':' . $this->view);
            $source = $processor->process($source);
            benchmark('view::' . $name, $this->namespace . ':' . $this->view);
        }

        return $source;
    }
}