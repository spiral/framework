<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

interface ProcessorInterface
{
    /**
     * New processors instance with options specified in view config.
     *
     * @param array        $options
     * @param LayeredCompiler $compiler View component instance (if presented).
     */
    public function __construct(array $options, LayeredCompiler $compiler = null);

    /**
     * Performs view code pre-processing. View component will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source    View source (code).
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @return string
     */
    public function processSource($source, $namespace, $view);
}