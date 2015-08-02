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
use Spiral\Components\View\Compiler\Processors\Templater\TemplaterException;
use Spiral\Components\View\ViewException;
use Spiral\Components\View\ViewManager;
use Spiral\Support\Html\HtmlTokenizer;

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
     * Token context.
     *
     * @var array
     */
    protected $token = [];

    /**
     * New instance of importer.
     *
     * @param Compiler          $compiler
     * @param TemplateProcessor $templater
     * @param array             $token
     */
    public function __construct(Compiler $compiler, TemplateProcessor $templater, array $token)
    {
        $attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];

        if (strpos($attributes['path'], $templater->getNSSeparator()) !== false)
        {
            list($this->namespace, $this->directory) = $templater->fetchLocation(
                $attributes['path'],
                $token
            );
        }
        else
        {
            $this->namespace = $compiler->getNamespace();
            $this->directory = $attributes['path'];
        }

        $this->directory = rtrim($this->directory, '/*');
        $this->token = $token;

        $this->outerNamespace = $attributes['namespace'];

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
        try
        {
            $views = $viewManager->getViews($this->namespace);
        }
        catch (ViewException $exception)
        {
            throw new TemplaterException(
                $exception->getMessage(),
                $this->token,
                $exception->getCode(),
                $exception
            );
        }

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
            throw new TemplaterException(
                "No views were found under directory '{$this->directory}' in namespace '{$this->namespace}'.",
                $this->token
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