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

class NativeImporter implements ImporterInterface
{
    /**
     * Html tag name.
     *
     * @var string
     */
    protected $element = '';

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

        $this->element = $attributes['native'];
    }

    /**
     * Check if element (tag) has to be imported.
     *
     * @param string $element
     * @return bool
     */
    public function isImported($element)
    {
        if ($this->element == '*')
        {
            //To disable every importer, you can still define more importers after that
            return true;
        }

        return strtolower($element) == strtolower($this->element);
    }

    /**
     * Get imported element namespace.
     *
     * @param string $element
     * @return string
     */
    public function getNamespace($element)
    {
        return null;
    }

    /**
     * Get imported element view name.
     *
     * @param string $element
     * @return string
     */
    public function getView($element)
    {
        return null;
    }
}