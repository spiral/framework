<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

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
     * View component instance.
     *
     * @var ViewManager
     */
    protected $view = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param array       $options
     * @param ViewManager $compiler View component instance (if presented).
     */
    public function __construct(array $options, ViewManager $compiler = null)
    {
        $this->options = $options + $this->options;
        $this->view = $compiler;
    }

    /**
     * Will replace static variables in view source with their values or empty string if not specified.
     * Default pattern if @{variable|default} can be redefined in view config.
     *
     * This variables can be used to redefine layout, browser support or switch to mobile version.
     *
     * @param string $source    View source (code).
     * @param string $view      View name.
     * @param string $namespace View namespace.
     * @return string
     */
    public function processSource($source, $view, $namespace)
    {
        //Doing replacement
        return preg_replace_callback($this->options['pattern'], array($this, 'replace'), $source);
    }

    /**
     * Getting static variable value.
     *
     * @param array $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->view->staticVariable($matches['name'])
            ? $this->view->staticVariable($matches['name'])
            : (isset($matches['default']) ? $matches['default'] : '');
    }
}