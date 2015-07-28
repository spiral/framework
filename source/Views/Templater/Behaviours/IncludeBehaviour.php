<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\TemplateProcessor;
use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Support\Html\HtmlTokenizer;

class IncludeBehaviour implements BehaviourInterface
{
    /**
     * Associated templater.
     *
     * @var TemplateProcessor
     */
    protected $templater = null;

    /**
     * View namespace to be imported.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * View to be imported.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Import context includes everything between opening and closing tag.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Context token.
     *
     * @var array
     */
    protected $token = [];

    /**
     * User able to define custom attributes while importing element, this attributes will be treated
     * as node blocks.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Include behaviour mount external view source and inner block under parent Node, it will
     * additionally keep import context (everything between opening and closing tag) and tag
     * attributes as node sblocks.
     *
     * @param TemplateProcessor $templater
     * @param string            $namespace
     * @param string            $view
     * @param array             $context
     * @param array             $token
     */
    public function __construct(
        TemplateProcessor $templater,
        $namespace,
        $view,
        array $context,
        array $token = []
    )
    {
        $this->templater = $templater;

        $this->namespace = $namespace;
        $this->view = $view;

        $this->context = $context;

        $this->token = $token;
        $this->attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];
    }

    /**
     * Pack node context (everything between open and close tag).
     *
     * @return Node
     */
    public function getContext()
    {
        $context = '';

        foreach ($this->context as $token)
        {
            $context .= $token[HtmlTokenizer::TOKEN_CONTENT];
        }

        return new Node($this->templater, $this->templater->uniqueName(), $context);
    }

    /**
     * Create imported node to be included into parent container (node).
     *
     * @return Node
     */
    public function createNode()
    {
        $node = $this->templater->createNode($this->namespace, $this->view, '', $this->token);

        //Let's register user defined blocks (context and attributes) as placeholders
        $node->registerBlock('context', [], [$this->createPlaceholder('context', $contextID)], true);

        foreach ($this->attributes as $attribute => $value)
        {
            $node->registerBlock($attribute, [], [$value], true);
        }

        //We now have to compile node content to pass it's body to parent node
        $content = $node->compile($compiled, $outerBlocks);

        //Some blocks (usually user attributes) can be exported to template using non default
        //rendering technique, for example every "extra" attribute can be passed to specific
        //template location.
        $content = $this->templater->exportBlocks($content, $outerBlocks);

        //Let's not parse complied content without any imports (to prevent collision)
        $templater = clone $this->templater;
        $templater->flushImporters();

        $rebuilt = new Node($templater, $templater->uniqueName(), $content);

        //Now we can mount our blocks
        if ($contextBlock = $rebuilt->findBlock($contextID))
        {
            $contextBlock->addNode($this->getContext());
        }

        return $rebuilt;
    }

    /**
     * Create placeholder block.
     *
     * @param string $name
     * @param string $blockID
     * @return string
     */
    protected function createPlaceholder($name, &$blockID)
    {
        $blockID = $name . '-' . $this->templater->uniqueName();

        return '${' . $blockID . '}';
    }
}