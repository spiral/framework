<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\BlockBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\ExtendBehaviour;
use Spiral\Components\View\Compiler\Processors\Templater\Behaviours\IncludeBehaviour;
use Spiral\Support\Html\Tokenizer;

class Node
{
    /**
     * Name of block used to represent import context.
     */
    const CONTEXT_BLOCK = 'context';

    /**
     * Short tags expression, usually used inside attributes and etc.
     */
    const SHORT_TAGS = '/\${(?P<name>[a-z0-9_\.\-]+)(?: *\| *(?P<default>[^}]+) *)?}/i';

    /**
     * NodeSupervisor is responsible for resolve tag behaviours.
     *
     * @invisible
     * @var NodeSupervisorInterface
     */
    protected $supervisor = null;

    /**
     * Node name (usually related to block name).
     *
     * @var string
     */
    protected $name = '';

    /**
     * Indication that node extended parent layout/node, meaning custom blocks can not be rendered
     * outside defined parent layout.
     *
     * @var bool
     */
    protected $extended = false;

    /**
     * Set of child nodes being used during rendering.
     *
     * @var string[]|Node[]
     */
    protected $nodes = [];

    /**
     * Set of blocks defined outside parent scope (parent layout blocks), blocks like either dynamic
     * or used for internal template reasons. They should not be rendered in plain HTML.
     *
     * @var Node[]
     */
    protected $outerBlocks = [];

    public function __construct(NodeSupervisorInterface $supervisor, $name, $source = [])
    {
        $this->supervisor = $supervisor;

        $this->name = $name;

        if (is_string($source))
        {
            $source = Tokenizer::parseSource($source);
        }

        $this->parseTokens($source);
    }

    public function getSupervisor()
    {
        return $this->supervisor;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function parseTokens(array $tokens)
    {
        //Current active token
        $activeToken = [];

        //Some blocks can be named as parent. We have to make sure we closing the correct one
        $activeLevel = 0;

        //Content to represent full tag declaration (including body)
        $activeContent = [];

        foreach ($tokens as $token)
        {
            $tokenType = $token[Tokenizer::TOKEN_TYPE];

            if (empty($activeToken))
            {
                switch ($tokenType)
                {
                    case Tokenizer::TAG_SHORT:
                        $this->registerToken($token);
                        break;

                    case Tokenizer::TAG_OPEN:
                        $activeToken = $token;
                        break;

                    case Tokenizer::TAG_CLOSE:
                        throw new TemplaterException(
                            "Unpaired close tag '{$token[Tokenizer::TOKEN_NAME]}'."
                        );
                        break;
                    case Tokenizer::PLAIN_TEXT:
                        //Everything outside any tag
                        $this->registerContent([$token]);
                        break;
                }

                continue;
            }

            if (
                $tokenType != Tokenizer::PLAIN_TEXT
                && $token[Tokenizer::TOKEN_NAME] == $activeToken[Tokenizer::TOKEN_NAME]
            )
            {
                if ($tokenType == Tokenizer::TAG_OPEN)
                {
                    $activeContent[] = $token;
                    $activeLevel++;
                }
                elseif ($tokenType == Tokenizer::TAG_CLOSE)
                {
                    if ($activeLevel === 0)
                    {
                        //Closing current token
                        $this->registerToken($activeToken, $activeContent, $token);
                        $activeToken = $activeContent = [];
                    }
                    else
                    {
                        $activeContent[] = $token;
                        $activeLevel--;
                    }
                }
                else
                {
                    //Short tag with same name (used to call for parent content)s
                    $activeContent[] = $token;
                }

                continue;
            }

            //Collecting token content
            $activeContent[] = $token;
        }

        //Everything after last tag
        $this->registerContent($activeContent);
    }

    protected function registerToken(array $token, array $content = [], array $closeToken = [])
    {
        $behaviour = $this->supervisor->getBehaviour($token, $content, $this);

        //Let's check token behaviour to understand how to handle this token
        if ($behaviour === BehaviourInterface::SKIP_TOKEN)
        {
            //This is some technical tag (import and etc)
            return;
        }

        if ($behaviour === BehaviourInterface::SIMPLE_TAG)
        {
            //Nothing really to do with this tag
            $this->registerContent([$token]);

            //Let's parse inner content
            $this->parseTokens($content);

            !empty($closeToken) && $this->registerContent([$closeToken]);

            return;
        }

        //Now we have to process more complex behaviours
        $this->handleBehaviour($behaviour, $content);
    }

    public function handleBehaviour(BehaviourInterface $behaviour, array $content = [])
    {
        if ($behaviour instanceof ExtendBehaviour)
        {
            //We have to copy nodes from parent (?)
            $this->nodes = $behaviour->getParent()->nodes;

            //Indication that this node has parent, meaning we have to handle blocks little
            //bit different way
            $this->extended = true;

            foreach ($behaviour->getAttributes() as $attributes => $value)
            {
                $this->registerBlock($attributes, $value);
            }

            return;
        }

        if ($behaviour instanceof BlockBehaviour)
        {
            //Registering block
            $this->registerBlock($behaviour->getName(), $content);

            return;
        }

        if ($behaviour instanceof IncludeBehaviour)
        {
            $this->nodes[] = $behaviour->getNode();
        }
    }

    /**
     * Find a children node by name.
     *
     * @param string $name
     * @return Node|null
     */
    public function findBlock($name)
    {
        foreach ($this->nodes as $node)
        {
            if ($node instanceof self && $node->name)
            {
                if ($node->name === $name)
                {
                    return $node;
                }

                if ($found = $node->findBlock($name))
                {
                    return $found;
                }
            }
        }

        return null;
    }

    public function registerBlock($name, $content, $parsed = [])
    {
        $node = new Node($this->supervisor, $name, $content);

        if (!empty($parsed))
        {
            $node->nodes = $parsed;
        }

        if (!$this->extended)
        {
            $this->nodes[] = $node;

            return;
        }

        if (empty($parent = $this->findBlock($name)))
        {
            //New blocks can not be registered outside parent scope should not be rendered but need
            //to know
            //$node->outer = true;
            //array_unshift($this->nodes, $node);

            $this->outerBlocks[] = $node;

            return;
        }

        //We have to replace parent content with extended blocks
        $parent->replace($node);
    }

    protected function replace(Node $node)
    {
        if (!empty($inner = $node->findBlock($this->name)))
        {
            //This construction allows child block use parent content
            $inner->nodes = $this->nodes;
        }

        $this->nodes = $node->nodes;
    }

    protected function registerContent($content)
    {
        if ($this->extended || empty($content))
        {
            //No blocks or text can exists outside parent template
            return;
        }

        if (is_array($content))
        {
            $plainContent = '';
            foreach ($content as $token)
            {
                $plainContent .= $token[Tokenizer::TOKEN_CONTENT];
            }

            $content = $plainContent;
        }

        //Looking for short tag definitions
        if (preg_match(self::SHORT_TAGS, $content, $matches))
        {
            $chunks = explode($matches[0], $content);

            //We expecting first chunk to be string
            $this->registerContent(array_shift($chunks));

            $this->registerBlock(
                $matches['name'],
                isset($matches['default']) ? $matches['default'] : ''
            );

            //Rest of content
            $this->registerContent(join($matches[0], $chunks));

            return;
        }

        if (is_string(end($this->nodes)))
        {
            $this->nodes[key($this->nodes)] .= $content;

            return;
        }

        $this->nodes[] = $content;
    }

    public function compile(&$compiled = [], &$outerBlocks = [])
    {
        //We have to pre-compile outer nodes first
        foreach ($this->outerBlocks as $node)
        {
            if ($node instanceof self && !array_key_exists($node->name, $compiled))
            {
                //Node was never compiled
                $outerBlocks[$node->name] = $compiled[$node->name] = $node->compile($compiled);
            }
        }

        $result = '';
        foreach ($this->nodes as $node)
        {
            if (is_string($node))
            {
                $result .= $node;
                continue;
            }

            if (!array_key_exists($node->name, $compiled))
            {
                //Node was never compiled
                $compiled[$node->name] = $node->compile($compiled);
            }

            $result .= $compiled[$node->name];
        }

        return $result;
    }
}