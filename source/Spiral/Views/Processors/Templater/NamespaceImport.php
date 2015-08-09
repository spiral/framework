<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors\Templater;

use Spiral\Templater\Exceptions\TemplaterException;
use Spiral\Templater\HtmlTokenizer;
use Spiral\Templater\ImportInterface;
use Spiral\Templater\Templater;
use Spiral\Views\Exceptions\ViewException;
use Spiral\Views\Processors\TemplateProcessor;
use Spiral\Views\ViewManager;

/**
 * NamespaceImport allows user to create virtual namespace associated with external folder or namespace
 * in spiral views. This import generally used when module or view bundle provides big set of virtual
 * tags.
 *
 * Namespace import expects "path" token attribute points to external namespace or directory and
 * "namespace" with name of outer namespace (: will be added automatically).
 *
 * Attention! Will work only TemplateProcessor.
 */
class NamespaceImport implements ImportInterface
{
    /**
     * Aliases generation takes some time, we can cache it due bundle can be called multiple times.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Source view namespace.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * Source view directory (under declared namespace).
     *
     * @var string
     */
    private $directory = '';

    /**
     * User defined outer namespace.
     *
     * @var string
     */
    private $outerNamespace = '';

    /**
     * Generated (full tag name) element aliases associated with their locations.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * {@inheritdoc}
     *
     * @throws TemplaterException
     */
    public function __construct(Templater $templater, array $token)
    {
        $attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];

        if (!$templater instanceof TemplateProcessor) {
            throw new TemplaterException("NamespaceImport must be executed using TemplateProcessor.");
        }

        if (strpos($attributes['path'], $templater->getOptions()['nsSeparator']) !== false) {
            //Path includes namespace, we can fetch it regular way
            list($this->namespace, $this->directory) = $templater->fetchLocation(
                $attributes['path'], $token
            );
        } else {
            $this->namespace = $templater->getCompiler()->getNamespace();
            $this->directory = $attributes['path'];
        }

        $this->directory = rtrim($this->directory, '/*');
        $this->outerNamespace = $attributes['namespace'];

        //Let's generate set of aliases
        $this->buildAliases($templater->getViews(), $templater, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function isImported($element, array $token)
    {
        return isset($this->aliases[$element]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation($element, array $token)
    {
        //Location: [namespace, viewName]
        return [$this->namespace, $this->aliases[$element]];
    }

    /**
     * Generate view aliases based on provided view namespace and directory under such namespace.
     *
     * @param ViewManager       $viewManager
     * @param TemplateProcessor $templater
     * @param array             $token
     */
    protected function buildAliases(
        ViewManager $viewManager,
        TemplateProcessor $templater,
        array $token
    ) {
        if (isset(self::$cache[$this->importID()])) {
            //Already generated
            $this->aliases = self::$cache[$this->importID()];

            return;
        }

        try {
            $views = $viewManager->getViews($this->namespace);
        } catch (ViewException $exception) {
            //Unable to generate import
            throw new TemplaterException(
                $exception->getMessage(), $token, $exception->getCode(), $exception
            );
        }

        foreach ($views as $view => $engine) {
            if (!empty($this->directory) && strpos($view, $this->directory) !== 0) {
                //Different directory
                continue;
            }

            //Remove directory from view name
            $alias = ltrim(substr($view, strlen($this->directory)), '/');

            //Replace path separator (must be normalized) with tag path separator (usually .)
            $alias = str_replace('/', $templater->getOptions()['separator'], $alias);

            //View alias = namespace:view.subView
            $alias = $this->outerNamespace . $templater->getOptions()['nsSeparator'] . $alias;

            $this->aliases[$alias] = $view;
        }

        if (empty($this->aliases)) {
            throw new TemplaterException(
                "No views were found under directory '{$this->directory}' in namespace '{$this->namespace}'.",
                $token
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
    private function importID()
    {
        return $this->namespace . '.' . $this->directory . '.' . $this->outerNamespace;
    }
}