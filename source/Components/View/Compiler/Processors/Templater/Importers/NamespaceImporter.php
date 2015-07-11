<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Importers;

use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\Processors\TemplateProcessor;
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;

class NamespaceImporter implements ImporterInterface
{
    /**
     * Imported view namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Imported view.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Element alias (can be plain html tag name).
     *
     * @var string
     */
    protected $alias = '';

    /**
     * Is importer definitive.
     *
     * @var bool
     */
    protected $definitive = false;

    /**
     * New instance of importer.
     *
     * @param Compiler          $compiler
     * @param TemplateProcessor $templater
     * @param array             $options
     */
    public function __construct(Compiler $compiler, TemplateProcessor $templater, array $options)
    {
        dump($options);

//        list($this->namespace, $this->view) = $templater->fetchLocation(
//            $options['path'],
//            [Tokenizer::TOKEN_ATTRIBUTES => $options]
//        );

        if ($this->namespace == 'self')
        {
    //        $this->namespace = $compiler->getNamespace();
        }

//        $this->alias = $options['namespace'];
       $this->definitive = array_key_exists('definitive', $options);
    }

    /**
     * Definitive imports allows developer to create custom element aliases in a scope of element
     * import (sub-tags).
     *
     * @return bool
     */
    public function isDefinitive()
    {
        return $this->definitive;
    }

    /**
     * Check if element (tag) has to be imported.
     *
     * @param string $element
     * @return bool
     */
    public function isImported($element)
    {

    }

    /**
     * Get imported element namespace.
     *
     * @param string $element
     * @return string
     */
    public function getNamespace($element)
    {

    }

    /**
     * Get imported element view name.
     *
     * @param string $element
     * @return string
     */
    public function getView($element)
    {

    }
}