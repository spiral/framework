<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\BlockBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\ExtendsBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\IncludeBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\ExporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Exporters\AttributeExporter;
use Spiral\Components\View\Compiler\Processors\Templater\Exporters\JsonExporter;
use Spiral\Components\View\Compiler\Processors\Templater\Exporters\PHPExporter;
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\AliasedImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\BundleImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\NamespaceImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\NativeImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Components\View\Compiler\Processors\Templater\SupervisorInterface;
use Spiral\Components\View\Compiler\Processors\Templater\TemplaterException;
use Spiral\Components\View\ViewException;
use Spiral\Components\View\ViewManager;
use Spiral\Support\Html\Tokenizer;

class TemplateProcessor implements ProcessorInterface, SupervisorInterface
{
    /**
     * Primary token types supported by spiral.
     */
    const TYPE_BLOCK = 'block';
    const TYPE_EXTENDS = 'extends';
    const TYPE_USE   = 'use';
    const TYPE_INCLUDE = 'include';

    /**
     * Used to create unique node names when required.
     *
     * @var int
     */
    protected static $index = 0;

    /**
     * View manager.
     *
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * Associated compiler.
     *
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * Templater rendering options, syntax and names.
     *
     * @var array
     */
    protected $options = [
        'strictMode'  => false,
        'separator'   => '.',
        'nsSeparator' => ':',
        'prefixes'    => [
            self::TYPE_BLOCK   => ['block:', 'section:'],
            self::TYPE_EXTENDS => ['extends:'],
            self::TYPE_USE     => ['use']
        ],
        'imports'     => [
            AliasedImporter::class   => ['path', 'as'],
            NamespaceImporter::class => ['path', 'namespace'],
            BundleImporter::class    => ['bundle'],
            NativeImporter::class    => ['native']
        ],
        'keywords'    => [
            'namespace' => ['view:namespace', 'node:namespace'],
            'view'      => ['view:parent', 'node:parent']
        ],
        'exporters'   => [
            AttributeExporter::class,
            JsonExporter::class,
            PHPExporter::class
        ]
    ];

    /**
     * Set of active templater importers used to resolve if specified tag should be treated as
     * include from foreign view.
     *
     * @var ImporterInterface[]
     */
    protected $importers = [];

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager $viewManager
     * @param Compiler    $compiler SpiralCompiler instance.
     * @param array       $options
     */
    public function __construct(ViewManager $viewManager, Compiler $compiler, array $options)
    {
        $this->viewManager = $viewManager;
        $this->compiler = $compiler;
        $this->options = $options + $this->options;

        //Enable or disable exceptions on corrupted HTML
        Node::strictMode($this->options['strictMode']);
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source View source (code).
     * @return string
     * @throws TemplaterException
     */
    public function process($source)
    {
        try
        {
            $root = new Node($this, '@root', $source);
        }
        catch (TemplaterException $exception)
        {
            throw $this->clarifyException($exception);
        }

        return $root->compile();
    }

    /**
     * Get unique node name, unique names are required in some cases to correctly process includes
     * and etc.
     *
     * @return string
     */
    public function uniqueName()
    {
        return md5(self::$index++);
    }

    /**
     * Get string or character used to separate view directories.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->options['separator'];
    }

    /**
     * Get string or character used to separate view and it's namespace.
     *
     * @return string
     */
    public function getNSSeparator()
    {
        return $this->options['nsSeparator'];
    }

    /**
     * Active templater imports.
     *
     * @return ImporterInterface[]
     */
    public function getImporters()
    {
        return $this->importers;
    }

    /**
     * Define html tag behaviour based on supervisor syntax settings.
     *
     * @param array $token
     * @param array $content
     * @param Node  $node
     * @return mixed|BehaviourInterface
     */
    public function getBehaviour(array $token, array $content, Node $node)
    {
        $attributes = [];
        if (!empty($token[Tokenizer::TOKEN_ATTRIBUTES]))
        {
            $attributes = $token[Tokenizer::TOKEN_ATTRIBUTES];
        }

        switch ($type = $this->tokenType($token, $name))
        {
            case self::TYPE_BLOCK:
                return new BlockBehaviour($name);
                break;
            case self::TYPE_EXTENDS:
                list($namespace, $view) = $this->fetchLocation($name, $token);
                $extends = new ExtendsBehaviour(
                    $this->createNode($namespace, $view, '', $token),
                    $attributes
                );

                //We have to combine parent imports with local one
                $this->importers = $extends->getImporters();

                //Sending command to extend parent
                return $extends;
                break;
            case self::TYPE_USE:
                $this->registerImporter($attributes, $token);

                //No need to include use tag into source
                return BehaviourInterface::SKIP_TOKEN;
                break;
        }

        //We now have to decide if element points to external view to be imported
        foreach ($this->importers as $importer)
        {
            if ($importer->isImported($name))
            {
                if ($importer instanceof NativeImporter)
                {
                    //Native importer tells us to treat this element as simple html
                    break;
                }

                return new IncludeBehaviour(
                    $this,
                    $importer->getNamespace($name),
                    $importer->getView($name),
                    $content,
                    $token[Tokenizer::TOKEN_ATTRIBUTES]
                );
            }
        }

        return BehaviourInterface::SIMPLE_TAG;
    }

    /**
     * Helper method used to define tag type based on defined templater syntax.
     *
     * @param string $token
     * @param string $name Tag name stripped from prefix will go there.
     * @return int|null|string
     */
    protected function tokenType($token, &$name)
    {
        $name = $token[Tokenizer::TOKEN_NAME];
        foreach ($this->options['prefixes'] as $type => $prefixes)
        {
            foreach ($prefixes as $prefix)
            {
                if (strpos($name, $prefix) === 0)
                {
                    $name = substr($name, strlen($prefix));

                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Fetch valid namespace and view name using token name and it's attributes. Based on different
     * situations different attributes and constructions can be used. Token attributes are usually
     * used in extends or use tags.
     *
     * @param string $name
     * @param array  $token
     * @return array
     */
    public function fetchLocation($name, array $token = [])
    {
        $namespace = $this->compiler->getNamespace();
        $view = str_replace($this->options['separator'], '/', $name);

        if (strpos($view, $this->options['nsSeparator']) !== false)
        {
            //Namespace can be redefined by tag name
            list($namespace, $view) = explode($this->options['nsSeparator'], $view);
            if (empty($namespace))
            {
                $namespace = $this->compiler->getNamespace();
            }
        }

        if (!empty($token))
        {
            foreach ($token[Tokenizer::TOKEN_ATTRIBUTES] as $attribute => $value)
            {
                if (in_array($attribute, $this->options['keywords']['namespace']))
                {
                    //Namespace can be redefined from attribute
                    $namespace = $value;
                }

                if (in_array($attribute, $this->options['keywords']['view']))
                {
                    //Overwriting view
                    $view = $value;
                }
            }
        }

        return [$namespace, $view];
    }

    /**
     * Create node using specified namespace and view. Node content will be preprocessed using
     * detached compiler and new instance of templater with it's local imports.
     *
     * @param string $namespace
     * @param string $view
     * @param string $name  If not specified unique name will be used.
     * @param array  $token Token are required only for clarifying location of exceptions.
     * @return Node
     */
    public function createNode($namespace, $view, $name = '', array $token = [])
    {
        try
        {
            $compiler = $this->compiler->cloneCompiler($namespace, $view);
        }
        catch (ViewException $exception)
        {
            throw $this->clarifyException($exception, $token);
        }

        //We have to pre-compile view
        $source = $compiler->getSource();

        //We have to pass parent content thought every processor which is located before templater
        foreach ($compiler->getProcessors() as $processor)
        {
            if ($processor instanceof self)
            {
                //The rest will be handled by TemplateProcessor
                break;
            }

            $source = $processor->process($source);
        }

        if (empty($processor))
        {
            throw $this->clarifyException(
                new TemplaterException("Invalid processors chain.", $token),
                $token
            );
        }

        return new Node($processor, !empty($name) ? $name : $this->uniqueName(), $source);
    }

    /**
     * Helper method used to parse and register new importer defined in view code.
     *
     * @param array $attributes
     * @param array $token
     */
    protected function registerImporter(array $attributes, array $token = [])
    {
        $importer = null;
        foreach ($this->options['imports'] as $class => $keywords)
        {
            if (count(array_intersect_key(array_flip($keywords), $attributes)) === count($keywords))
            {
                $importer = $class;
                break;
            }
        }

        if (empty($importer))
        {
            throw $this->clarifyException(
                new TemplaterException("Undefined importer type.", $token),
                $token
            );
        }

        //Last import has higher priority than first import
        $this->addImporter(new $importer($this->compiler, $this, $attributes));
    }

    /**
     * Add new importer.
     *
     * @param ImporterInterface $importer
     */
    public function addImporter(ImporterInterface $importer)
    {
        array_unshift($this->importers, $importer);
    }

    /**
     * Some blocks (usually user attributes) can be exported to template using non default rendering
     * technique, for example every "extra" attribute can be passed to specific template location.
     *
     * @param string $content
     * @param array  $blocks
     * @return string
     */
    public function exportBlocks($content, array $blocks)
    {
        foreach ($this->options['exporters'] as $exporter)
        {
            /**
             * @var ExporterInterface $exporter
             */
            $exporter = new $exporter($content, $blocks);

            //Exporting
            $content = $exporter->mountBlocks();
        }

        return $content;
    }

    /**
     * Clarify exception location to point to place where token was located. Only view exceptions
     * can be clarified.
     *
     * @param ViewException $exception
     * @param array         $token
     * @return ViewException
     */
    protected function clarifyException(ViewException $exception, array $token = [])
    {
        if (empty($token) && $exception instanceof TemplaterException)
        {
            $token = $exception->getToken();
        }

        if (empty($token))
        {
            //Exception location can not be clarified
            return $exception;
        }

        //We need separate compiler
        $compiler = $this->compiler->cloneCompiler(
            $this->compiler->getNamespace(),
            $this->compiler->getView()
        );

        //We now can find file exception got raised
        $source = $compiler->getSource();

        //We have to process view source to make sure that it has same state as at moment of error
        foreach ($compiler->getProcessors() as $processor)
        {
            if ($processor instanceof self)
            {
                //The rest will be handled by TemplateProcessor
                break;
            }

            $source = $processor->process($source);
        }

        //We will need only first tag line
        $target = explode("\n", $token[Tokenizer::TOKEN_CONTENT])[0];

        //Let's try to locate place where exception was used
        $lines = explode("\n", $source);

        foreach ($lines as $number => $line)
        {
            if (strpos($line, $target) !== false)
            {
                //We found where token were used
                $exception->setLocation($this->compiler->getFilename(), $number + 1);

                return $exception;
            }
        }

        return $exception;
    }
}