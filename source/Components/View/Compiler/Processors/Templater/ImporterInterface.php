<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\Processors\TemplateProcessor;

/**
 * ImportInterface used by templater to defined what tags should be treated as includes and how to
 * resolve their view or namespace.
 */
interface ImporterInterface
{
    /**
     * New instance of importer.
     *
     * @param Compiler $compiler
     * @param TemplateProcessor $templater
     * @param array             $token
     */
    public function __construct(Compiler $compiler, TemplateProcessor $templater, array $token);

    /**
     * Definitive imports allows developer to create custom element aliases in a scope of element
     * import (sub-tags).
     *
     * @return bool
     */
    public function isDefinitive();

    /**
     * Check if element (tag) has to be imported.
     *
     * @param string $element
     * @return bool
     */
    public function isImported($element);

    /**
     * Get imported element namespace.
     *
     * @param string $element
     * @return string
     */
    public function getNamespace($element);

    /**
     * Get imported element view name.
     *
     * @param string $element
     * @return string
     */
    public function getView($element);
}