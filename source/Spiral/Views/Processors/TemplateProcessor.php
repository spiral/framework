<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Templater\Exceptions\TemplaterException;
use Spiral\Templater\Exporters\AttributeExporter;
use Spiral\Templater\HtmlTokenizer;
use Spiral\Templater\Imports\StopImport;
use Spiral\Templater\Node;
use Spiral\Templater\Templater;
use Spiral\Views\Compiler;
use Spiral\Views\Exceptions\ViewException;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewManager;

/**
 * Extends Templater to convert it into view Processors to be user inside spiral compiler.
 */
class TemplateProcessor extends Templater implements ProcessorInterface
{
    /**
     * Location constants.
     */
    const LOCATION_NAMESPACE = 0;
    const LOCATION_VIEW      = 1;

    /**
     * {@inheritdoc}
     *
     * Template processor adds namespace and view separators, and import elements.
     */
    protected $options = [
        'strictMode'  => false,
        'separator'   => '.',
        'nsSeparator' => ':',
        'prefixes'    => [
            self::TYPE_BLOCK   => ['block:', 'section:', 'yield:', 'define:'],
            self::TYPE_EXTENDS => ['extends:'],
            self::TYPE_IMPORT  => ['use', 'import']
        ],
        'imports'     => [
            AliasedImport::class   => ['path', 'as'],
            NamespaceImport::class => ['path', 'namespace'],
            BundleImport::class    => ['bundle'],
            StopImport::class      => ['stop']
        ],
        'keywords'    => [
            'namespace' => ['view:namespace', 'node:namespace'],
            'view'      => ['view:parent', 'node:parent']
        ],
        'exporters'   => [
            AttributeExporter::class,
        ]
    ];

    /**
     * @var ViewManager
     */
    protected $views = null;

    /**
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(ViewManager $views, Compiler $compiler, array $options)
    {
        $this->views = $views;
        $this->compiler = $compiler;

        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     *
     * @throws TemplaterException
     */
    public function process($source)
    {
        try {
            $root = new Node($this, '@root', $source);
        } catch (\Exception $exception) {
            throw $this->clarifyException($exception);
        }

        return $root->compile();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchLocation($name, array $token = [])
    {
        $namespace = $this->compiler->getNamespace();

        //We can't use slashes to separate view folders
        $view = str_replace($this->options['separator'], '/', $name);

        if (strpos($view, $this->options['nsSeparator']) !== false) {
            //Namespace can be redefined by tag name
            list($namespace, $view) = explode($this->options['nsSeparator'], $view);
            if (empty($namespace)) {
                $namespace = $this->compiler->getNamespace();
            }
        }

        if (!empty($token)) {
            foreach ($token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value) {
                if (in_array($attribute, $this->options['keywords']['namespace'])) {
                    //Namespace can be redefined from attribute
                    $namespace = $value;
                }

                if (in_array($attribute, $this->options['keywords']['view'])) {
                    //Overwriting view
                    $view = $value;
                }
            }
        }

        if ($namespace == 'self') {
            $namespace = $this->compiler->getNamespace();
        }

        return [$namespace, $view];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerImport($name, array $token)
    {
        $attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];

        $import = null;
        foreach ($this->options['imports'] as $class => $keywords) {
            //Trying to detect import type by specified keywords in attributes
            if (
                count(array_intersect_key(array_flip($keywords), $attributes)) === count($keywords)
            ) {
                $import = $class;
                break;
            }
        }

        if (empty($import)) {
            throw $this->clarifyException(
                new TemplaterException("Undefined importer type.", $token)
            );
        }

        //Last import has higher priority than first import
        $this->addImport(new $import($this->compiler, $this, $token));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSource($location, Templater &$templater = null, array $token = [])
    {
        try {
            $compiler = $this->compiler->reconfigure(
                $location[self::LOCATION_NAMESPACE],
                $location[self::LOCATION_VIEW]
            );
        } catch (ViewException $exception) {
            throw $this->clarifyException($exception, $token);
        }

        //We have to pre-compile view
        $source = $compiler->getSource();

        //We have to pass parent content thought every processor which is located before templater
        foreach ($compiler->getProcessors() as $processor) {
            if ($processor instanceof self) {
                //The rest will be handled by TemplateProcessor
                $templater = $processor;
                break;
            }

            $source = $processor->process($source);
        }

        if (empty($processor)) {
            throw $this->clarifyException(new TemplaterException(
                "Invalid processors chain, no TemplateProcessor found.", $token
            ));
        }

        return $source;
    }

    /**
     * Must create instance of TemplaterException using exception instance and context token. Might
     * clarify exception location in html code.
     *
     * @param \Exception $exception
     * @param array      $token
     * @return TemplaterException
     */
    protected function clarifyException(\Exception $exception, array $token = [])
    {
        if (!$exception instanceof TemplaterException) {
            $exception = new TemplaterException(
                $exception->getMessage(),
                [],
                $exception->getCode(),
                $exception
            );
        } elseif (empty($token)) {
            //We can fetch token from exception itself, thx Node
            $token = $exception->getToken();
        }

        if (empty($token)) {
            //Exception location can not be clarified
            return $exception;
        }

        //We want new instance of compiler (for isolation)
        //todo: check if required, is it similar to get source?
        $compiler = $this->compiler->reconfigure(
            $this->compiler->getNamespace(),
            $this->compiler->getView()
        );

        //We now can find file exception got raised
        $source = $compiler->getSource();

        //We have to process view source to make sure that it has same state as at moment of error
        foreach ($compiler->getProcessors() as $processor) {
            if ($processor instanceof self) {
                //The rest will be handled by TemplateProcessor
                break;
            }

            $source = $processor->process($source);
        }

        //We will need only first tag line
        $target = explode("\n", $token[HtmlTokenizer::TOKEN_CONTENT])[0];

        //Let's try to locate place where exception was used
        $lines = explode("\n", $source);

        foreach ($lines as $number => $line) {
            if (strpos($line, $target) !== false) {

                //We found where token were used (!!)
                $exception->setLocation($this->compiler->getFilename(), $number + 1);

                return $exception;
            }
        }

        return $exception;
    }
}