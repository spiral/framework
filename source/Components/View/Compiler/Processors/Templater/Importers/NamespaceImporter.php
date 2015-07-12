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
use Spiral\Components\View\ViewException;
use Spiral\Components\View\ViewManager;
use Spiral\Support\Html\Tokenizer;

class NamespaceImporter implements ImporterInterface
{
    /**
     * Aliases generation takes some time, we can cache it.
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * View namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Namespace directory.
     *
     * @var string
     */
    protected $directory = '/';

    /**
     * User defined namespace.
     *
     * @var string
     */
    protected $outerNamespace = '';

    /**
     * Element aliases.
     *
     * @var array
     */
    protected $aliases = [];

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
        if (strpos($options['path'], $templater->getNSSeparator()) !== false)
        {
            list($this->namespace, $this->directory) = $templater->fetchLocation(
                $options['path'],
                [Tokenizer::TOKEN_ATTRIBUTES => $options]
            );
        }
        else
        {
            $this->namespace = $options['path'];
        }

        if ($this->namespace == 'self')
        {
            $this->namespace = $compiler->getNamespace();
        }

        $this->directory = rtrim($this->directory, '/*');

        $this->outerNamespace = $options['namespace'];
        $this->definitive = array_key_exists('definitive', $options);

        $this->buildAliases($compiler->getViewManager(), $templater);
    }

    /**
     * Aliases generated based on provided namespace and path.
     *
     * @param ViewManager       $viewManager
     * @param TemplateProcessor $templater
     */
    protected function buildAliases(ViewManager $viewManager, TemplateProcessor $templater)
    {
        if (isset(self::$cache[$this->importID()]))
        {
            $this->aliases = self::$cache[$this->importID()];

            return;
        }

        $views = $viewManager->getViews($this->namespace);
        foreach ($views as $view => $engine)
        {
            if (!empty($this->directory) && strpos($view, $this->directory) === false)
            {
                //Not aliased
                continue;
            }

            //View path should be already normalized
            $alias = $this->outerNamespace . $templater->getNSSeparator() . str_replace(
                    '/',
                    $templater->getSeparator(),
                    ltrim(substr($view, strlen($this->directory)), '/')
                );

            $this->aliases[$alias] = $view;
        }

        if (empty($this->aliases))
        {
            throw new ViewException(
                "No views were found under directory '{$this->directory}' in namespace '{$this->namespace}'."
            );
        }

        //Caching
        self::$cache[$this->importID()] = $this->aliases;
    }

    /**
     * Import ID used to speed up alises generation.
     *
     * @return string
     */
    protected function importID()
    {
        return $this->namespace . '.' . $this->directory . '.' . $this->outerNamespace;
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
        return isset($this->aliases[$element]);
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
        return $this->aliases[$element];
    }
}