<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Behaviours;

use Spiral\Stempler\BehaviourInterface;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\Node;
use Spiral\Stempler\Supervisor;

/**
 * Replaces specified block (including tag) with external node, automatically uses inner tag
 * content as "context" block and all other tag attributes as additional node child.
 */
class IncludeBehaviour implements BehaviourInterface
{
    /**
     * Name of block used to represent import context.
     */
    const CONTEXT_BLOCK = 'context';

    /**
     * Path to be included (see Supervisor createNode).
     *
     * @var string
     */
    protected $path = '';

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
     * @var Supervisor
     */
    protected $supervisor = null;

    /**
     * @param Supervisor $supervisor
     * @param string     $path
     * @param array      $context
     * @param array      $token
     */
    public function __construct(Supervisor $supervisor, $path, array $context, array $token = [])
    {
        $this->supervisor = $supervisor;
        $this->path = $path;

        $this->context = $context;
        $this->token = $token;
    }

    /**
     * Create node to be injected into template at place of tag caused import.
     *
     * @return Node
     */
    public function createNode()
    {
        //Content of node to be imported
        $node = $this->supervisor->createNode($this->path, $this->token);

        //Let's register user defined blocks (context and attributes) as placeholders
        $node->mountBlock(
            self::CONTEXT_BLOCK,
            [],
            [$this->createPlaceholder(self::CONTEXT_BLOCK, $contextID)],
            true
        );

        foreach ($this->token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value) {
            //Attributes counted as blocks to replace elements in included node
            $node->mountBlock($attribute, [], [$value], true);
        }

        //We now have to compile node content to pass it's body to parent node
        $content = $node->compile($dynamic);

        //Outer blocks (usually user attributes) can be exported to template using non default
        //rendering technique, for example every "extra" attribute can be passed to specific
        //template location. Stempler to decide.
        foreach ($this->supervisor->syntax()->blockExporters() as $exporter) {
            $content = $exporter->mountBlocks($content, $dynamic);
        }

        //Let's parse complied content without any imports (to prevent collision)
        $supervisor = clone $this->supervisor;
        $supervisor->flushImporters();

        //Outer content must be protected using unique names
        $rebuilt = new Node($supervisor, $supervisor->uniquePlaceholder(), $content);

        if (!empty($contextBlock = $rebuilt->findNode($contextID))) {
            //Now we can mount our content block
            $contextBlock->mountNode($this->contextNode());
        }

        return $rebuilt;
    }

    /**
     * Pack node context (everything between open and close tag).
     *
     * @return Node
     */
    protected function contextNode()
    {
        $context = '';
        foreach ($this->context as $token) {
            $context .= $token[HtmlTokenizer::TOKEN_CONTENT];
        }

        return new Node($this->supervisor, $this->supervisor->uniquePlaceholder(), $context);
    }

    /**
     * Create placeholder block (to be injected with inner blocks defined in context).
     *
     * @param string $name
     * @param string $blockID
     * @return string
     */
    protected function createPlaceholder($name, &$blockID)
    {
        $blockID = $name . '-' . $this->supervisor->uniquePlaceholder();

        //Short block declaration syntax
        return '${' . $blockID . '}';
    }
}