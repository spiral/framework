<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\View\DefaultCompiler;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class VariablesProcessor implements ProcessorInterface
{
    /**
     * Static variables replace options.
     *
     * @var array
     */
    protected $options = array(
        'pattern' => '/@\{(?P<name>[a-z0-9_\.\-]+)(?: *\| *(?P<default>[^}]+))?}/i'
    );

    /**
     * ViewManager component instance.
     *
     * @var ViewManager
     */
    protected $manager = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param DefaultCompiler $compiler Compiler instance.
     * @param array           $options
     */
    public function __construct(DefaultCompiler $compiler, array $options)
    {
        $this->manager = $compiler->getViewManager();
        $this->options = $options + $this->options;
    }

    /**
     * Performs view code pre-processing. View component will provide view source into processors,
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
        //Doing replacement
        return preg_replace_callback(
            $this->options['pattern'],
            array($this, 'replace'),
            $source
        );
    }

    /**
     * Getting static variable value.
     *
     * @param array $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->manager->staticVariable($matches['name'])
            ? $this->manager->staticVariable($matches['name'])
            : (isset($matches['default']) ? $matches['default'] : '');
    }
}