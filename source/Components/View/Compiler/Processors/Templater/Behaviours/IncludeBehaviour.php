<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Components\View\Compiler\Processors\Templater\NodeSupervisor;
use Spiral\Support\Html\Tokenizer;

class IncludeBehaviour implements BehaviourInterface
{
    protected $supervisor = null;

    protected $attributes = [];

    protected $context = [];

    protected $name = '';

    public function __construct(NodeSupervisor $supervisor, $name, array $context, array $attributes = [])
    {
        $this->supervisor = $supervisor;
        $this->context = $context;
        $this->attributes = $attributes;
        $this->name = $name;
    }

    public function getNode()
    {
        $node = new Node($this->supervisor, uniqid());

        //Change that
        $node->handleBehaviour(
            new ExtendBehaviour(
                new Node($this->supervisor, '', $this->supervisor->getSource($this->name)),
                []
            )
        );

        $node->registerBlock('context', [], [$this->getContext()]);

        foreach ($this->getAttributes() as $attribute => $value)
        {
            $node->registerBlock($attribute, [], [$value]);
        }

        return new Node($this->supervisor, uniqid(), $node->compile());
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        $context = '';

        foreach ($this->context as $token)
        {
            $context .= $token[Tokenizer::TOKEN_CONTENT];
        }

        return $context;
    }

    public function __debugInfo()
    {
        return (object)[
            'attributes' => $this->attributes,
            'context'    => $this->getContext()
        ];
    }
}