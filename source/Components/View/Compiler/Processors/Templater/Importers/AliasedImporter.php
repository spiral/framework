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
use Spiral\Support\Html\Tokenizer;

class AliasedImporter implements ImporterInterface
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
     * New instance of importer.
     *
     * @param Compiler          $compiler
     * @param TemplateProcessor $templater
     * @param array             $token
     */
    public function __construct(Compiler $compiler, TemplateProcessor $templater, array $token)
    {
        $attributes = $token[Tokenizer::TOKEN_ATTRIBUTES];

        list($this->namespace, $this->view) = $templater->fetchLocation(
            $attributes['path'],
            $token
        );

        if ($this->namespace == 'self')
        {
            $this->namespace = $compiler->getNamespace();
        }

        $this->alias = $attributes['as'];
    }

    /**
     * Check if element (tag) has to be imported.
     *
     * @param string $element
     * @return bool
     */
    public function isImported($element)
    {
        return strtolower($element) == strtolower($this->alias);
    }

    /**
     * Get imported element namespace.
     *
     * @param string $element
     * @return string
     */
    public function getNamespace($element)
    {
        return $this->namespace;
    }

    /**
     * Get imported element view name.
     *
     * @param string $element
     * @return string
     */
    public function getView($element)
    {
        return $this->view;
    }
}