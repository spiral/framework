<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Files\FilesInterface;
use Spiral\Views\Exceptions\CompilerException;

/**
 * Compiler supported by ViewManager and used to pre-render view content. In additional default
 * spiral View implementation can only work with compilers.
 *
 * Compilers must be designed to render/give content to view in a form of php file.
 */
interface CompilerInterface
{
    /**
     * @param ViewManager $views
     * @param FilesInterface $files
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @param string $filename  View filename.
     */
    public function __construct(
        ViewManager $views,
        FilesInterface $files,
        $namespace,
        $view,
        $filename
    );

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @return string
     */
    public function getView();

    /**
     * True if view has been already compiled and cached somewhere.
     *
     * @return bool
     */
    public function isCompiled();

    /**
     * @throws CompilerException
     * @throws \Exception
     */
    public function compile();

    /**
     * View filename location (to be rendered using require + export method or similar).
     *
     * @return string
     */
    public function compiledFilename();
}