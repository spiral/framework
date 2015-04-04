<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

interface CompilerInterface
{
    /**
     * Instance of view compiler. Compilers used to pre-process view files for faster rendering in
     * runtime environment.
     *
     * @param ViewManager $viewManager
     * @param string      $namespace View namespace.
     * @param string      $view      View name.
     * @param string      $source    Non-compiled source.
     * @param string      $input     View filename.
     * @param string      $output    Cached view filename (can be empty or not exists).
     */
    public function __construct(
        ViewManager $viewManager,
        $namespace,
        $view,
        $source,
        $input = '',
        $output = ''
    );

    /**
     * Compile original view file to plain php code.
     *
     * @return string
     */
    public function compile();
}