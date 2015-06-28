<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\View\LayeredCompiler;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Core\Component;

class ExpressionProcessor implements ProcessorInterface
{
    /**
     * Static variables replace options.
     *
     * @var array
     */
    protected $expressions = array();

    /**
     * ViewManager component instance.
     *
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager     $viewManager
     * @param LayeredCompiler $compiler Compiler instance.
     * @param array           $options
     */
    public function __construct(ViewManager $viewManager, LayeredCompiler $compiler, array $options)
    {
        $this->viewManager = $viewManager;
        $this->expressions = $options['expressions'];
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source    View source (code).
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @param string $input     Input filename (usually real view file).
     * @param string $output    Output filename (usually view cache, target file may not exists).
     * @return string
     */
    public function processSource($source, $namespace, $view, $input = '', $output = '')
    {
        foreach ($this->expressions as $expression)
        {
            $source = preg_replace_callback($expression['pattern'], $expression['callback'], $source);
        }

        return $source;
    }

    /**
     * Embedded replacer used to set static variable or it's default value.
     *
     * @param array $matches
     * @return string
     */
    public function staticVariable(array $matches)
    {
        return $this->viewManager->getVariable(
            $matches['name'],
            !empty($matches['default']) ? $matches['default'] : ''
        );
    }
}