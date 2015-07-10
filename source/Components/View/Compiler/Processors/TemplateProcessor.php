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
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\AliasedImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\BundleImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\DefinitiveImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\NamespaceImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Importers\NativeImporter;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Components\View\Compiler\Processors\Templater\SupervisorInterface;
use Spiral\Components\View\Compiler\Processors\Templater\TemplaterException;
use Spiral\Components\View\ViewManager;
use Spiral\Support\Html\Tokenizer;

class TemplateProcessor implements ProcessorInterface, SupervisorInterface
{
    /**
     * Primary token types supported by spiral.
     */
    const TYPE_BLOCK   = 'block';
    const TYPE_EXTENDS = 'extends';
    const TYPE_USE     = 'use';
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
        'separator'   => '.',
        'nsSeparator' => ':',
        'prefixes'    => [
            self::TYPE_BLOCK   => ['block:', 'section:'],
            self::TYPE_EXTENDS => ['extends:'],
            self::TYPE_USE     => ['use']
        ],
        'imports'     => [
            AliasedImporter::class    => ['path', 'as'],
            NamespaceImporter::class  => ['path', 'namespace'],
            BundleImporter::class     => ['bundle'],
            NativeImporter::class     => ['native'],
            DefinitiveImporter::class => ['path', 'internal']
        ],
        'keywords'    => [
            'namespace' => ['view:namespace', 'node:namespace'],
            'view'      => ['view:parent', 'node:parent']
        ]
    ];

    /**
     * Set of active templater imports used to resolve if specified tag should be treated as
     * include from foreign view.
     *
     * @var ImporterInterface[]
     */
    public $imports = [];

    /**
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
        $root = new Node($this, '@root', $source);

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
     * Active templater imports.
     *
     * @return ImporterInterface[]
     */
    public function getImports()
    {
        return $this->imports;
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
                    $this->createNode($namespace, $view, $token),
                    $attributes
                );

                //We have to combine parent imports with local one
                $this->imports = $extends->getImports();

                //Sending command to extend parent
                return $extends;
                break;
            case self::TYPE_USE:
                $this->registerImport($attributes, $token);
                //REGISTER USE

                array_unshift($this->imports, [
                    $token[Tokenizer::TOKEN_ATTRIBUTES]['path'],
                    $token[Tokenizer::TOKEN_ATTRIBUTES]['as']
                ]);

                //No need to include use tag into source
                return BehaviourInterface::SKIP_TOKEN;
                break;
        }

        if ($name == 'include')
        {
            foreach ($this->imports as $alias)
            {
                if ($name == $alias[1])
                {
                    $name = $alias[0];
                    break;
                }
            }

            return new IncludeBehaviour(
                $this,
                $name,
                $content,
                $token[Tokenizer::TOKEN_ATTRIBUTES]
            );
        }

        //Includes has to handled separately

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
    protected function fetchLocation($name, array $token = [])
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
     * @param string $name
     * @param array  $token
     * @return Node
     */
    public function createNode($namespace, $view, $name = '', array $token = [])
    {
        $compiler = $this->compiler->createCompiler($namespace, $view);

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
            throw new TemplaterException("Invalid processors chain.");
        }

        return new Node($processor, $name, $source);
    }

    protected function registerImport(array $attributes, array $token = [])
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
            throw new TemplaterException("Undefined importer type.");
        }

        $this->importers[] = new $importer($this->viewManager, $this, $attributes);
    }

    public function getNode($name, $view)
    {
        $compiler = $this->compiler->createCompiler('default', $view);

        //We have to pre-compile view
        $source = $compiler->getSource();

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
            throw new TemplaterException("Invalid processors chain.");
        }

        return new Node($processor, $name, $source);
    }


    public function mountOuterBlocks($content, array $blocks)
    {

        //TODO: CHANGE IT, ADD MORE CLASSES

        if (preg_match_all(
            '/ node:attributes(=[\'"]'
            . '(?:include:(?P<include>[a-z_\-,]+))?\|?'
            . '(?:exclude:(?P<exclude>[a-z_\-,]+))?[\'"])?/i',
            $content,
            $matches
        ))
        {
            foreach ($matches[0] as $id => $replace)
            {
                //$include = $matches['include'][$id] ? explode(',', $matches['include'][$id]) : [];
                //$exclude = $matches['exclude'][$id] ? explode(',', $matches['exclude'][$id]) : [];

                //Rendering (yes, we can render this part during collecting, 5 lines to top), but i
                //want to do it like this, cos it will be more flexible to add more features in future
                foreach ($blocks as $name => $value)
                {
                    $blocks[$name] = $name . '="' . $value . '"';
                }

                $content = str_replace($replace, $blocks ? ' ' . join(' ', $blocks) : '', $content);
            }
        }

        return $content;
    }
}