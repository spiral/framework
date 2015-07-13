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
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\ExtendsBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Support\Html\Tokenizer;

class BundleImporter implements ImporterInterface
{
    /**
     * Bundle view namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Bundle view name.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Importers fetched from view bundle.
     *
     * @var ImporterInterface[]
     */
    protected $importers = [];

    /**
     * Context token to catch errors.
     *
     * @var array
     */
    protected $token = [];

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
     * @param array             $token
     */
    public function __construct(Compiler $compiler, TemplateProcessor $templater, array $token)
    {
        $attributes = $token[Tokenizer::TOKEN_ATTRIBUTES];
        list($this->namespace, $this->view) = $templater->fetchLocation(
            $attributes['bundle'],
            $token
        );

        if ($this->namespace == 'self')
        {
            $this->namespace = $compiler->getNamespace();
        }

        $this->token = $token;
        $this->definitive = array_key_exists('definitive', $attributes);

        $this->buildAliases($templater);
    }

    /**
     * Aliases generated based on provided view bundle file.
     *
     * @param TemplateProcessor $templater
     */
    protected function buildAliases(TemplateProcessor $templater)
    {
        //We need node for our view to parse imports
        $node = new Node($templater, $templater->uniqueName());

        //Let's exclude node content
        $node->handleBehaviour(new ExtendsBehaviour(
            $include = $templater->createNode($this->namespace, $this->view, '', $this->token),
            []
        ));

        /**
         * @var TemplateProcessor $includeTemplater
         */
        $includeTemplater = $include->getSupervisor();

        //We can fetch all importers from our bundle view
        $this->importers = $includeTemplater->getImporters();
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
        foreach ($this->importers as $importer)
        {
            if ($importer->isImported($element))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get imported element namespace.
     *
     * @param string $element
     * @return string
     */
    public function getNamespace($element)
    {
        foreach ($this->importers as $importer)
        {
            if ($importer->isImported($element))
            {
                return $importer->getNamespace($element);
            }
        }

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
        foreach ($this->importers as $importer)
        {
            if ($importer->isImported($element))
            {
                return $importer->getView($element);
            }
        }

        return null;
    }
}